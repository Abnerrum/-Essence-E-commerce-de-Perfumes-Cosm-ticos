<?php
// ============================================================
// CartService.php — Lógica do carrinho de compras
// ============================================================
namespace App\Services;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Coupon;
use App\Models\Product;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class CartService
{
    /**
     * Retorna ou cria o carrinho para o usuário/sessão atual.
     */
    public function getCart(): Cart
    {
        $cart = null;

        if (Auth::check()) {
            // Usuário logado: busca/cria por user_id
            $cart = Cart::firstOrCreate(['user_id' => Auth::id()]);

            // Mescla carrinho de sessão anônima ao logar
            $sessionId = Session::get('cart_session_id');
            if ($sessionId) {
                $anonCart = Cart::with('items')->where('session_id', $sessionId)->first();
                if ($anonCart) {
                    foreach ($anonCart->items as $item) {
                        $this->add(Product::find($item->product_id), $item->quantity, $cart);
                    }
                    $anonCart->delete();
                    Session::forget('cart_session_id');
                }
            }
        } else {
            // Visitante: carrinho por session_id
            $sessionId = Session::get('cart_session_id') ?? Session::getId();
            Session::put('cart_session_id', $sessionId);
            $cart = Cart::firstOrCreate(['session_id' => $sessionId]);
        }

        return $cart->load('items.product.images');
    }

    /**
     * Adiciona produto ao carrinho. Incrementa quantidade se já existir.
     */
    public function add(Product $product, int $quantity = 1, ?Cart $cart = null): void
    {
        $cart ??= $this->getCart();

        $item = $cart->items()->where('product_id', $product->id)->first();

        if ($item) {
            $newQty = min($item->quantity + $quantity, 10); // máx 10 por produto
            $item->update(['quantity' => $newQty]);
        } else {
            $cart->items()->create([
                'product_id' => $product->id,
                'quantity'   => min($quantity, 10),
                'unit_price' => $product->current_price,
            ]);
        }
    }

    /**
     * Atualiza quantidade de um item do carrinho.
     */
    public function update(int $itemId, int $quantity): void
    {
        $cart = $this->getCart();
        $item = $cart->items()->findOrFail($itemId);
        $item->update(['quantity' => max(1, min($quantity, 10))]);
    }

    /**
     * Remove item do carrinho.
     */
    public function remove(int $itemId): void
    {
        $cart = $this->getCart();
        $cart->items()->findOrFail($itemId)->delete();
    }

    /**
     * Limpa o carrinho após finalizar pedido.
     */
    public function clear(): void
    {
        $cart = $this->getCart();
        $cart->items()->delete();
        Session::forget('checkout.coupon');
    }

    /**
     * Retorna total de itens no carrinho (para badge do header).
     */
    public function getItemsCount(): int
    {
        return $this->getCart()->items->sum('quantity');
    }

    /**
     * Aplica cupom de desconto ao carrinho.
     */
    public function applyCoupon(string $code): array
    {
        $coupon = Coupon::where('code', strtoupper($code))
            ->where('active', true)
            ->where(fn($q) => $q->whereNull('expires_at')->orWhere('expires_at', '>=', today()))
            ->first();

        if (!$coupon) {
            return ['success' => false, 'message' => 'Cupom inválido ou expirado.'];
        }

        $cart = $this->getCart();
        $subtotal = $cart->items->sum(fn($i) => $i->quantity * $i->unit_price);

        if ($coupon->min_purchase && $subtotal < $coupon->min_purchase) {
            return [
                'success' => false,
                'message' => "Valor mínimo para este cupom: R$ " . number_format($coupon->min_purchase, 2, ',', '.'),
            ];
        }

        if ($coupon->max_uses && $coupon->used_count >= $coupon->max_uses) {
            return ['success' => false, 'message' => 'Cupom esgotado.'];
        }

        Session::put('checkout.coupon', $coupon->id);

        return ['success' => true, 'coupon' => $coupon];
    }

    /**
     * Retorna resumo do carrinho para responses JSON.
     */
    public function getSummary(): array
    {
        $cart  = $this->getCart();
        $total = $cart->items->sum(fn($i) => $i->quantity * $i->unit_price);

        return [
            'items_count' => $cart->items->sum('quantity'),
            'total'       => $total,
            'total_formatted' => 'R$ ' . number_format($total, 2, ',', '.'),
        ];
    }
}

// ============================================================
// PaymentService.php — Integração com Mercado Pago + criação de pedido
// ============================================================
namespace App\Services;

use App\Models\Cart;
use App\Models\Address;
use App\Models\Order;
use App\Models\Coupon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;

class PaymentService
{
    /**
     * Cria o pedido no banco e inicia o pagamento.
     */
    public function createOrder(Cart $cart, Address $address, array $paymentData): Order
    {
        return DB::transaction(function () use ($cart, $address, $paymentData) {
            $subtotal = $cart->items->sum(fn($i) => $i->quantity * $i->unit_price);
            $shipping = $this->calculateShipping(Session::get('checkout.shipping', 'PAC'));
            $discount = $this->getCouponDiscount($subtotal);
            $total    = max(0, $subtotal + $shipping - $discount);

            // Cria o pedido
            $order = Order::create([
                'user_id'              => Auth::id(),
                'status'               => 'pending',
                'subtotal'             => $subtotal,
                'shipping_cost'        => $shipping,
                'discount'             => $discount,
                'total'                => $total,
                'shipping_zip'         => $address->zip_code,
                'shipping_street'      => $address->street,
                'shipping_number'      => $address->number,
                'shipping_complement'  => $address->complement,
                'shipping_neighborhood'=> $address->neighborhood,
                'shipping_city'        => $address->city,
                'shipping_state'       => $address->state,
                'payment_method'       => $paymentData['payment_method'],
                'shipping_service'     => Session::get('checkout.shipping', 'PAC'),
            ]);

            // Cria os itens do pedido e decrementa estoque
            foreach ($cart->items as $item) {
                $order->items()->create([
                    'product_id'   => $item->product_id,
                    'product_name' => $item->product->name,
                    'quantity'     => $item->quantity,
                    'unit_price'   => $item->unit_price,
                    'total_price'  => $item->quantity * $item->unit_price,
                ]);

                $item->product->decrement('stock', $item->quantity);
            }

            // Marca cupom como usado
            if ($couponId = Session::get('checkout.coupon')) {
                Coupon::find($couponId)?->increment('used_count');
                Session::forget('checkout.coupon');
            }

            // Inicia pagamento no Mercado Pago (se necessário)
            if ($paymentData['payment_method'] !== 'boleto') {
                $this->initMercadoPago($order, $paymentData);
            }

            return $order;
        });
    }

    private function calculateShipping(string $service): float
    {
        // Simulação de frete (integrar com Correios/Melhor Envio em produção)
        return match ($service) {
            'SEDEX' => 29.90,
            'PAC'   => 12.90,
            default => 0.0,
        };
    }

    private function getCouponDiscount(float $subtotal): float
    {
        $couponId = Session::get('checkout.coupon');
        if (!$couponId) return 0.0;

        $coupon = Coupon::find($couponId);
        if (!$coupon) return 0.0;

        return match ($coupon->type) {
            'percent' => round($subtotal * $coupon->value / 100, 2),
            'fixed'   => min($coupon->value, $subtotal),
            default   => 0.0,
        };
    }

    private function initMercadoPago(Order $order, array $paymentData): void
    {
        // Integração com SDK do Mercado Pago
        // Documentação: https://www.mercadopago.com.br/developers/pt/docs
        //
        // $mp = new \MercadoPago\Client\Payment\PaymentClient();
        // $response = $mp->create([...]);
        // $order->update(['payment_id' => $response->id]);
    }
}

// ============================================================
// StockService.php — Controle de estoque
// ============================================================
namespace App\Services;

use App\Models\Product;

class StockService
{
    public function reserve(int $productId, int $quantity): bool
    {
        $product = Product::lockForUpdate()->find($productId);

        if (!$product || $product->stock < $quantity) {
            return false;
        }

        $product->decrement('stock', $quantity);
        return true;
    }

    public function release(int $productId, int $quantity): void
    {
        Product::find($productId)?->increment('stock', $quantity);
    }

    public function isAvailable(int $productId, int $quantity = 1): bool
    {
        return Product::where('id', $productId)
            ->where('stock', '>=', $quantity)
            ->exists();
    }
}
