@extends('layouts.app')

@section('title', 'Meu Carrinho')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">

    <h1 class="font-serif text-3xl font-bold text-stone-900 mb-8">Meu Carrinho</h1>

    @if($cart->items->isEmpty())
    {{-- Carrinho vazio --}}
    <div class="text-center py-24">
        <div class="text-6xl mb-6">🛍️</div>
        <h2 class="font-serif text-2xl font-semibold text-stone-700 mb-3">Seu carrinho está vazio</h2>
        <p class="text-stone-500 mb-8">Explore nosso catálogo e encontre o perfume perfeito para você.</p>
        <a href="{{ route('products.index') }}" class="bg-rose-500 text-white px-8 py-3 rounded-full font-medium hover:bg-rose-600 transition-colors">
            Ver Produtos
        </a>
    </div>

    @else
    <div class="lg:grid lg:grid-cols-3 lg:gap-8">

        {{-- ============================================================
             ITENS DO CARRINHO
        ============================================================ --}}
        <div class="lg:col-span-2 space-y-4">
            @foreach($cart->items as $item)
            <div class="bg-white rounded-2xl border border-stone-200 p-4 flex gap-4 items-center"
                 x-data="cartItem({{ $item->id }})">

                {{-- Imagem --}}
                <a href="{{ route('products.show', $item->product->slug) }}" class="flex-shrink-0">
                    @if($item->product->primaryImage)
                    <img src="{{ Storage::url($item->product->primaryImage->path) }}"
                         alt="{{ $item->product->name }}"
                         class="w-20 h-20 object-cover rounded-xl">
                    @else
                    <div class="w-20 h-20 bg-rose-50 rounded-xl flex items-center justify-center text-3xl">🌸</div>
                    @endif
                </a>

                {{-- Info --}}
                <div class="flex-1 min-w-0">
                    <a href="{{ route('products.show', $item->product->slug) }}">
                        <h3 class="font-medium text-stone-900 hover:text-rose-600 transition-colors truncate">
                            {{ $item->product->name }}
                        </h3>
                    </a>
                    <p class="text-xs text-stone-500 mt-0.5">{{ $item->product->brand->name }} · {{ $item->product->volume }}</p>
                    <p class="text-rose-600 font-semibold mt-1">R$ {{ number_format($item->unit_price, 2, ',', '.') }}</p>
                </div>

                {{-- Quantidade --}}
                <div class="flex items-center gap-2">
                    <button @click="decrease()" :disabled="quantity <= 1"
                            class="w-8 h-8 rounded-full border border-stone-300 flex items-center justify-center text-stone-600 hover:border-rose-400 hover:text-rose-500 disabled:opacity-40 transition-colors">
                        −
                    </button>
                    <span x-text="quantity" class="w-6 text-center font-medium text-sm">{{ $item->quantity }}</span>
                    <button @click="increase()" :disabled="quantity >= 10"
                            class="w-8 h-8 rounded-full border border-stone-300 flex items-center justify-center text-stone-600 hover:border-rose-400 hover:text-rose-500 disabled:opacity-40 transition-colors">
                        +
                    </button>
                </div>

                {{-- Subtotal + Remover --}}
                <div class="text-right flex-shrink-0">
                    <p class="font-semibold text-stone-900" x-text="'R$ ' + (quantity * {{ $item->unit_price }}).toFixed(2).replace('.', ',')">
                        R$ {{ number_format($item->quantity * $item->unit_price, 2, ',', '.') }}
                    </p>
                    <form action="{{ route('cart.remove', $item->id) }}" method="POST" class="mt-1">
                        @csrf @method('DELETE')
                        <button type="submit" class="text-xs text-stone-400 hover:text-red-500 transition-colors">Remover</button>
                    </form>
                </div>
            </div>
            @endforeach
        </div>

        {{-- ============================================================
             RESUMO DO PEDIDO
        ============================================================ --}}
        <div class="mt-8 lg:mt-0">
            <div class="bg-white rounded-2xl border border-stone-200 p-6 sticky top-24">
                <h2 class="font-serif text-xl font-bold text-stone-900 mb-6">Resumo do Pedido</h2>

                {{-- Cupom --}}
                <form action="{{ route('cart.coupon') }}" method="POST" class="mb-6">
                    @csrf
                    <label class="text-sm font-medium text-stone-700 block mb-2">Cupom de Desconto</label>
                    <div class="flex gap-2">
                        <input type="text" name="code" placeholder="BEMVINDO10"
                               class="flex-1 border border-stone-300 rounded-xl px-3 py-2 text-sm focus:outline-none focus:border-rose-400 uppercase">
                        <button type="submit" class="bg-stone-900 text-white px-4 py-2 rounded-xl text-sm hover:bg-stone-700 transition-colors">
                            Aplicar
                        </button>
                    </div>
                    @error('coupon')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </form>

                {{-- Totais --}}
                <div class="space-y-3 text-sm border-t border-stone-100 pt-4">
                    <div class="flex justify-between text-stone-600">
                        <span>Subtotal ({{ $cart->items_count }} {{ Str::plural('item', $cart->items_count) }})</span>
                        <span>R$ {{ number_format($cart->total, 2, ',', '.') }}</span>
                    </div>
                    <div class="flex justify-between text-stone-600">
                        <span>Frete</span>
                        <span class="text-green-600">Calcular no checkout</span>
                    </div>
                    @if(session('checkout.coupon'))
                    <div class="flex justify-between text-green-600">
                        <span>Desconto (cupom)</span>
                        <span>− calculado no checkout</span>
                    </div>
                    @endif
                </div>

                <div class="flex justify-between font-bold text-lg text-stone-900 border-t border-stone-200 mt-4 pt-4">
                    <span>Total</span>
                    <span>R$ {{ number_format($cart->total, 2, ',', '.') }}</span>
                </div>

                <p class="text-xs text-stone-400 mt-2 mb-6">Ou em até 6x de R$ {{ number_format($cart->total / 6, 2, ',', '.') }} sem juros</p>

                @auth
                <a href="{{ route('checkout.index') }}"
                   class="block w-full text-center bg-rose-500 text-white py-3 rounded-full font-semibold hover:bg-rose-600 transition-colors">
                    Finalizar Compra
                </a>
                @else
                <a href="{{ route('login') }}"
                   class="block w-full text-center bg-rose-500 text-white py-3 rounded-full font-semibold hover:bg-rose-600 transition-colors">
                    Entrar para Comprar
                </a>
                @endauth

                <a href="{{ route('products.index') }}"
                   class="block w-full text-center text-stone-500 text-sm mt-3 hover:text-rose-500 transition-colors">
                    ← Continuar Comprando
                </a>
            </div>
        </div>
    </div>
    @endif
</div>

<script>
function cartItem(itemId) {
    return {
        quantity: {{ $item->quantity ?? 1 }},
        async decrease() {
            if (this.quantity > 1) {
                this.quantity--;
                await this.update();
            }
        },
        async increase() {
            if (this.quantity < 10) {
                this.quantity++;
                await this.update();
            }
        },
        async update() {
            await fetch(`/carrinho/atualizar/${itemId}`, {
                method: 'PATCH',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
                body: JSON.stringify({ quantity: this.quantity }),
            });
        }
    }
}
</script>
@endsection
