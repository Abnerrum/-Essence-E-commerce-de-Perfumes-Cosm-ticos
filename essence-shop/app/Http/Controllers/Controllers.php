<?php
// ============================================================
// ProductController.php — Catálogo e busca de produtos
// ============================================================
namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Category;
use App\Models\Brand;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    /**
     * Listagem com filtros: gênero, categoria, marca, preço, ordenação
     */
    public function index(Request $request)
    {
        $query = Product::with(['category', 'brand', 'images'])
            ->active()
            ->inStock();

        // Filtro de gênero
        if ($request->filled('gender')) {
            $query->forGender($request->gender);
        }

        // Filtro de categoria
        if ($request->filled('category')) {
            $query->whereHas('category', fn($q) => $q->where('slug', $request->category));
        }

        // Filtro de marca
        if ($request->filled('brand')) {
            $query->whereHas('brand', fn($q) => $q->where('slug', $request->brand));
        }

        // Filtro de preço
        if ($request->filled('price_min')) {
            $query->where('price', '>=', $request->price_min);
        }
        if ($request->filled('price_max')) {
            $query->where('price', '<=', $request->price_max);
        }

        // Busca por nome
        if ($request->filled('q')) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', "%{$request->q}%")
                  ->orWhere('description', 'like', "%{$request->q}%")
                  ->orWhereHas('brand', fn($b) => $b->where('name', 'like', "%{$request->q}%"));
            });
        }

        // Ordenação
        match ($request->sort) {
            'price_asc'   => $query->orderBy('price'),
            'price_desc'  => $query->orderByDesc('price'),
            'newest'      => $query->orderByDesc('created_at'),
            'rating'      => $query->orderByDesc('rating_avg'),
            default       => $query->orderByDesc('featured')->orderByDesc('created_at'),
        };

        $products   = $query->paginate(20)->withQueryString();
        $categories = Category::where('active', true)->orderBy('sort_order')->get();
        $brands     = Brand::where('active', true)->orderBy('name')->get();

        return view('products.index', compact('products', 'categories', 'brands'));
    }

    /**
     * Página de detalhe do produto
     */
    public function show(string $slug)
    {
        $product = Product::with(['category', 'brand', 'images', 'reviews.user'])
            ->where('slug', $slug)
            ->active()
            ->firstOrFail();

        $related = Product::with(['images'])
            ->active()
            ->where('category_id', $product->category_id)
            ->where('id', '!=', $product->id)
            ->inRandomOrder()
            ->limit(4)
            ->get();

        return view('products.show', compact('product', 'related'));
    }

    /**
     * Busca rápida via AJAX (para o header)
     */
    public function search(Request $request)
    {
        $results = Product::with(['images'])
            ->active()
            ->where('name', 'like', "%{$request->q}%")
            ->limit(6)
            ->get(['id', 'name', 'slug', 'price', 'price_sale']);

        return response()->json($results);
    }
}

// ============================================================
// CartController.php — Gerenciamento do carrinho
// ============================================================
class CartController extends Controller
{
    public function __construct(private \App\Services\CartService $cartService) {}

    public function index()
    {
        $cart = $this->cartService->getCart();
        return view('cart.index', compact('cart'));
    }

    public function add(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity'   => 'integer|min:1|max:10',
        ]);

        $product = Product::active()->inStock()->findOrFail($request->product_id);

        $this->cartService->add($product, $request->quantity ?? 1);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Produto adicionado ao carrinho!',
                'count'   => $this->cartService->getItemsCount(),
            ]);
        }

        return back()->with('success', 'Produto adicionado ao carrinho!');
    }

    public function update(Request $request, int $itemId)
    {
        $request->validate(['quantity' => 'required|integer|min:1|max:10']);
        $this->cartService->update($itemId, $request->quantity);

        return response()->json(['success' => true, 'cart' => $this->cartService->getSummary()]);
    }

    public function remove(int $itemId)
    {
        $this->cartService->remove($itemId);
        return back()->with('success', 'Item removido do carrinho.');
    }

    public function applyCoupon(Request $request)
    {
        $request->validate(['code' => 'required|string']);

        $result = $this->cartService->applyCoupon($request->code);

        return $result['success']
            ? back()->with('success', 'Cupom aplicado!')
            : back()->withErrors(['coupon' => $result['message']]);
    }
}

// ============================================================
// CheckoutController.php — Fluxo de compra
// ============================================================
class CheckoutController extends Controller
{
    public function __construct(
        private \App\Services\CartService $cartService,
        private \App\Services\PaymentService $paymentService,
    ) {}

    public function index()
    {
        $cart = $this->cartService->getCart();

        if ($cart->items->isEmpty()) {
            return redirect()->route('cart.index')->with('error', 'Seu carrinho está vazio.');
        }

        $addresses = auth()->user()->addresses()->orderByDesc('is_default')->get();

        return view('checkout.index', compact('cart', 'addresses'));
    }

    public function shipping(Request $request)
    {
        $request->validate([
            'address_id' => 'required|exists:addresses,id',
            'shipping'   => 'required|string', // "PAC" ou "SEDEX"
        ]);

        session(['checkout.address_id' => $request->address_id]);
        session(['checkout.shipping'   => $request->shipping]);

        return redirect()->route('checkout.payment');
    }

    public function payment()
    {
        return view('checkout.payment');
    }

    public function process(Request $request)
    {
        $request->validate([
            'payment_method' => 'required|in:pix,credit_card,boleto',
        ]);

        $cart    = $this->cartService->getCart();
        $address = auth()->user()->addresses()->findOrFail(session('checkout.address_id'));
        $order   = $this->paymentService->createOrder($cart, $address, $request->all());

        $this->cartService->clear();

        return redirect()->route('orders.show', $order)->with('success', 'Pedido realizado com sucesso!');
    }
}

// ============================================================
// OrderController.php — Histórico de pedidos
// ============================================================
class OrderController extends Controller
{
    public function index()
    {
        $orders = auth()->user()
            ->orders()
            ->with(['items.product'])
            ->latest()
            ->paginate(10);

        return view('orders.index', compact('orders'));
    }

    public function show(\App\Models\Order $order)
    {
        abort_if($order->user_id !== auth()->id(), 403);
        $order->load(['items.product.images']);
        return view('orders.show', compact('order'));
    }
}

// ============================================================
// Admin/ProductController.php — CRUD produtos no admin
// ============================================================
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Category;
use App\Models\Brand;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function index()
    {
        $products = Product::with(['category', 'brand'])
            ->latest()
            ->paginate(20);

        return view('admin.products.index', compact('products'));
    }

    public function create()
    {
        $categories = Category::where('active', true)->get();
        $brands     = Brand::where('active', true)->get();
        return view('admin.products.form', compact('categories', 'brands'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'category_id'   => 'required|exists:categories,id',
            'brand_id'      => 'required|exists:brands,id',
            'name'          => 'required|string|max:200',
            'description'   => 'nullable|string',
            'notes'         => 'nullable|string',
            'gender'        => 'required|in:male,female,unisex',
            'price'         => 'required|numeric|min:0',
            'price_sale'    => 'nullable|numeric|min:0',
            'sku'           => 'required|string|unique:products,sku',
            'stock'         => 'required|integer|min:0',
            'volume'        => 'nullable|string',
            'concentration' => 'nullable|string',
            'active'        => 'boolean',
            'featured'      => 'boolean',
            'new_arrival'   => 'boolean',
            'images.*'      => 'image|mimes:jpg,jpeg,png,webp|max:2048',
        ]);

        $product = Product::create($data);

        // Upload de imagens
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $index => $image) {
                $path = $image->store('products', 'public');
                $product->images()->create([
                    'path'       => $path,
                    'is_primary' => $index === 0,
                    'sort_order' => $index,
                ]);
            }
        }

        return redirect()->route('admin.products.index')
            ->with('success', 'Produto criado com sucesso!');
    }

    public function edit(Product $product)
    {
        $categories = Category::where('active', true)->get();
        $brands     = Brand::where('active', true)->get();
        return view('admin.products.form', compact('product', 'categories', 'brands'));
    }

    public function update(Request $request, Product $product)
    {
        $data = $request->validate([
            'category_id'   => 'required|exists:categories,id',
            'brand_id'      => 'required|exists:brands,id',
            'name'          => 'required|string|max:200',
            'description'   => 'nullable|string',
            'notes'         => 'nullable|string',
            'gender'        => 'required|in:male,female,unisex',
            'price'         => 'required|numeric|min:0',
            'price_sale'    => 'nullable|numeric|min:0',
            'sku'           => "required|string|unique:products,sku,{$product->id}",
            'stock'         => 'required|integer|min:0',
            'volume'        => 'nullable|string',
            'concentration' => 'nullable|string',
            'active'        => 'boolean',
            'featured'      => 'boolean',
            'new_arrival'   => 'boolean',
        ]);

        $product->update($data);

        return redirect()->route('admin.products.index')
            ->with('success', 'Produto atualizado com sucesso!');
    }

    public function destroy(Product $product)
    {
        $product->delete();
        return back()->with('success', 'Produto removido.');
    }
}
