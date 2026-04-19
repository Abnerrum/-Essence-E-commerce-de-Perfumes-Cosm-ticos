<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Essence Shop') — Perfumes & Cosméticos</title>
    <meta name="description" content="@yield('description', 'Perfumes e cosméticos masculinos e femininos com as melhores marcas do Brasil.')">

    {{-- Fonts --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;600;700&family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet">

    {{-- Tailwind CSS (via CDN em dev — usar Vite em produção) --}}
    <script src="https://cdn.tailwindcss.com"></script>

    {{-- Alpine.js --}}
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    {{-- Assets compilados --}}
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    @stack('styles')
</head>
<body class="bg-stone-50 text-gray-900 font-sans antialiased">

{{-- ============================================================
     HEADER
============================================================ --}}
<header class="bg-white border-b border-stone-200 sticky top-0 z-50" x-data="{ mobileMenu: false, searchOpen: false }">
    {{-- Top bar --}}
    <div class="bg-stone-900 text-stone-200 text-xs py-1.5 text-center">
        Frete grátis em compras acima de R$ 299 &nbsp;|&nbsp; Parcelamento em até 6x sem juros
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between h-16 gap-4">

            {{-- Logo --}}
            <a href="{{ route('home') }}" class="flex-shrink-0">
                <span class="font-serif text-2xl font-bold tracking-wider text-stone-900">Essence</span>
                <span class="text-rose-500 text-xs font-light tracking-widest block -mt-1 ml-0.5">PERFUMES & COSMÉTICOS</span>
            </a>

            {{-- Busca central (desktop) --}}
            <div class="hidden md:flex flex-1 max-w-xl mx-8" x-data="searchWidget()">
                <div class="relative w-full">
                    <input
                        type="text"
                        placeholder="Buscar perfumes, marcas..."
                        x-model="query"
                        @input.debounce.300ms="search()"
                        @focus="open = true"
                        @click.away="open = false"
                        class="w-full border border-stone-300 rounded-full pl-4 pr-10 py-2 text-sm focus:outline-none focus:border-rose-400 transition-colors"
                    >
                    <button class="absolute right-3 top-1/2 -translate-y-1/2 text-stone-400 hover:text-rose-500">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                    </button>

                    {{-- Dropdown de resultados --}}
                    <div x-show="open && results.length > 0" class="absolute top-full mt-2 w-full bg-white rounded-xl shadow-lg border border-stone-100 overflow-hidden">
                        <template x-for="product in results" :key="product.id">
                            <a :href="`/produtos/${product.slug}`" class="flex items-center gap-3 px-4 py-3 hover:bg-rose-50 transition-colors">
                                <span x-text="product.name" class="text-sm text-stone-800"></span>
                                <span x-text="'R$ ' + parseFloat(product.price_sale || product.price).toFixed(2).replace('.', ',')" class="ml-auto text-sm font-medium text-rose-600"></span>
                            </a>
                        </template>
                    </div>
                </div>
            </div>

            {{-- Ações --}}
            <div class="flex items-center gap-3">
                {{-- Wishlist --}}
                @auth
                <a href="{{ route('wishlist.index') }}" class="hidden sm:flex items-center text-stone-600 hover:text-rose-500 transition-colors">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
                    </svg>
                </a>

                {{-- Conta --}}
                <div class="relative" x-data="{ open: false }">
                    <button @click="open = !open" class="flex items-center gap-1.5 text-stone-600 hover:text-rose-500 transition-colors text-sm">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                        </svg>
                        <span class="hidden sm:inline">{{ auth()->user()->name }}</span>
                    </button>
                    <div x-show="open" @click.away="open = false" class="absolute right-0 mt-2 w-48 bg-white rounded-xl shadow-lg border border-stone-100 py-1 z-50">
                        <a href="{{ route('account.index') }}"  class="block px-4 py-2 text-sm text-stone-700 hover:bg-rose-50">Minha Conta</a>
                        <a href="{{ route('orders.index') }}"   class="block px-4 py-2 text-sm text-stone-700 hover:bg-rose-50">Meus Pedidos</a>
                        @role('admin')
                        <a href="{{ route('admin.dashboard') }}" class="block px-4 py-2 text-sm text-stone-700 hover:bg-rose-50">Painel Admin</a>
                        @endrole
                        <hr class="my-1 border-stone-100">
                        <form action="{{ route('logout') }}" method="POST">
                            @csrf
                            <button class="block w-full text-left px-4 py-2 text-sm text-stone-700 hover:bg-rose-50">Sair</button>
                        </form>
                    </div>
                </div>
                @else
                <a href="{{ route('login') }}" class="text-sm text-stone-600 hover:text-rose-500 transition-colors">Entrar</a>
                <a href="{{ route('register') }}" class="text-sm bg-rose-500 text-white px-4 py-1.5 rounded-full hover:bg-rose-600 transition-colors">Cadastrar</a>
                @endauth

                {{-- Carrinho --}}
                <a href="{{ route('cart.index') }}" class="relative flex items-center text-stone-600 hover:text-rose-500 transition-colors">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
                    </svg>
                    @php $cartCount = app(\App\Services\CartService::class)->getItemsCount(); @endphp
                    @if($cartCount > 0)
                    <span class="absolute -top-2 -right-2 bg-rose-500 text-white text-xs w-5 h-5 flex items-center justify-center rounded-full font-medium">
                        {{ $cartCount > 9 ? '9+' : $cartCount }}
                    </span>
                    @endif
                </a>
            </div>

        </div>
    </div>

    {{-- Navbar categorias --}}
    <nav class="border-t border-stone-100 hidden md:block">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center gap-8 h-10 text-sm">
                <a href="{{ route('products.index', ['gender' => 'female']) }}" class="text-stone-600 hover:text-rose-500 font-medium transition-colors">Feminino</a>
                <a href="{{ route('products.index', ['gender' => 'male']) }}"   class="text-stone-600 hover:text-rose-500 font-medium transition-colors">Masculino</a>
                <a href="{{ route('products.index', ['gender' => 'unisex']) }}" class="text-stone-600 hover:text-rose-500 font-medium transition-colors">Unissex</a>
                <a href="{{ route('products.index', ['category' => 'kits-femininos']) }}" class="text-stone-600 hover:text-rose-500 transition-colors">Kits</a>
                <a href="{{ route('products.index') }}"                          class="text-rose-500 font-medium">Promoções 🔥</a>
            </div>
        </div>
    </nav>
</header>

{{-- ============================================================
     FLASH MESSAGES
============================================================ --}}
@if(session('success'))
<div class="bg-green-50 border-b border-green-200 text-green-800 text-sm text-center py-2 px-4">
    {{ session('success') }}
</div>
@endif
@if(session('error'))
<div class="bg-red-50 border-b border-red-200 text-red-700 text-sm text-center py-2 px-4">
    {{ session('error') }}
</div>
@endif

{{-- ============================================================
     CONTEÚDO PRINCIPAL
============================================================ --}}
<main class="min-h-screen">
    @yield('content')
</main>

{{-- ============================================================
     FOOTER
============================================================ --}}
<footer class="bg-stone-900 text-stone-300 mt-16">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
            <div>
                <span class="font-serif text-2xl font-bold text-white tracking-wider">Essence</span>
                <p class="mt-3 text-sm text-stone-400 leading-relaxed">Os melhores perfumes e cosméticos masculinos e femininos, entregues com cuidado na sua porta.</p>
            </div>
            <div>
                <h4 class="font-semibold text-white mb-4">Loja</h4>
                <ul class="space-y-2 text-sm">
                    <li><a href="{{ route('products.index', ['gender' => 'female']) }}" class="hover:text-rose-400 transition-colors">Feminino</a></li>
                    <li><a href="{{ route('products.index', ['gender' => 'male']) }}" class="hover:text-rose-400 transition-colors">Masculino</a></li>
                    <li><a href="{{ route('products.index', ['gender' => 'unisex']) }}" class="hover:text-rose-400 transition-colors">Unissex</a></li>
                    <li><a href="{{ route('products.index') }}" class="hover:text-rose-400 transition-colors">Promoções</a></li>
                </ul>
            </div>
            <div>
                <h4 class="font-semibold text-white mb-4">Ajuda</h4>
                <ul class="space-y-2 text-sm">
                    <li><a href="#" class="hover:text-rose-400 transition-colors">Como comprar</a></li>
                    <li><a href="#" class="hover:text-rose-400 transition-colors">Prazo de entrega</a></li>
                    <li><a href="#" class="hover:text-rose-400 transition-colors">Trocas e devoluções</a></li>
                    <li><a href="#" class="hover:text-rose-400 transition-colors">Fale conosco</a></li>
                </ul>
            </div>
            <div>
                <h4 class="font-semibold text-white mb-4">Pagamento</h4>
                <div class="flex flex-wrap gap-2 text-xs">
                    <span class="bg-stone-800 px-2 py-1 rounded">PIX</span>
                    <span class="bg-stone-800 px-2 py-1 rounded">Boleto</span>
                    <span class="bg-stone-800 px-2 py-1 rounded">Visa</span>
                    <span class="bg-stone-800 px-2 py-1 rounded">Mastercard</span>
                    <span class="bg-stone-800 px-2 py-1 rounded">Elo</span>
                </div>
                <p class="mt-4 text-xs text-stone-500">Parcele em até 6x sem juros no cartão de crédito.</p>
            </div>
        </div>
        <div class="mt-10 pt-6 border-t border-stone-800 text-sm text-stone-500 text-center">
            © {{ date('Y') }} Essence Shop. Todos os direitos reservados.
        </div>
    </div>
</footer>

<script>
function searchWidget() {
    return {
        query: '',
        results: [],
        open: false,
        async search() {
            if (this.query.length < 2) { this.results = []; return; }
            const res = await fetch(`/busca?q=${encodeURIComponent(this.query)}`);
            this.results = await res.json();
            this.open = true;
        }
    }
}
</script>

@livewireScripts
@stack('scripts')
</body>
</html>
