@extends('layouts.app')

@section('title', 'Essence Shop — Perfumes & Cosméticos')

@section('content')

{{-- ============================================================
     HERO BANNER
============================================================ --}}
<section class="relative bg-gradient-to-br from-stone-900 via-stone-800 to-rose-950 text-white overflow-hidden">
    <div class="absolute inset-0 opacity-10">
        <div class="absolute top-0 right-0 w-96 h-96 bg-rose-400 rounded-full blur-3xl transform translate-x-32 -translate-y-16"></div>
        <div class="absolute bottom-0 left-0 w-64 h-64 bg-amber-300 rounded-full blur-3xl transform -translate-x-16 translate-y-8"></div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-24 md:py-32 relative">
        <div class="max-w-2xl">
            <p class="text-rose-400 text-sm font-medium tracking-widest uppercase mb-4">Nova Coleção 2025</p>
            <h1 class="font-serif text-5xl md:text-6xl font-bold leading-tight mb-6">
                A arte de se<br><span class="text-rose-400">perfumar</span> bem.
            </h1>
            <p class="text-stone-300 text-lg mb-8 leading-relaxed">
                Descubra fragrâncias únicas para cada momento da sua vida. Perfumes masculinos e femininos das melhores marcas do Brasil.
            </p>
            <div class="flex flex-wrap gap-4">
                <a href="{{ route('products.index', ['gender' => 'female']) }}"
                   class="bg-rose-500 hover:bg-rose-600 text-white px-8 py-3 rounded-full font-medium transition-all hover:scale-105">
                    Feminino
                </a>
                <a href="{{ route('products.index', ['gender' => 'male']) }}"
                   class="bg-white/10 hover:bg-white/20 text-white border border-white/20 px-8 py-3 rounded-full font-medium transition-all hover:scale-105">
                    Masculino
                </a>
            </div>
        </div>
    </div>
</section>

{{-- ============================================================
     CATEGORIAS EM DESTAQUE
============================================================ --}}
<section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
    <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
        <a href="{{ route('products.index', ['gender' => 'female']) }}"
           class="group relative rounded-2xl overflow-hidden bg-gradient-to-br from-rose-100 to-pink-200 p-8 hover:shadow-lg transition-all duration-300">
            <div class="absolute bottom-0 right-0 text-8xl opacity-20 transform translate-x-4 translate-y-4">🌸</div>
            <p class="text-xs text-rose-600 font-medium tracking-widest uppercase mb-2">Para ela</p>
            <h3 class="font-serif text-2xl font-bold text-rose-900 group-hover:text-rose-700 transition-colors">Feminino</h3>
            <p class="text-sm text-rose-700 mt-1">Florais, Orientais, Frutados</p>
        </a>

        <a href="{{ route('products.index', ['gender' => 'male']) }}"
           class="group relative rounded-2xl overflow-hidden bg-gradient-to-br from-stone-800 to-stone-900 p-8 hover:shadow-lg transition-all duration-300">
            <div class="absolute bottom-0 right-0 text-8xl opacity-20 transform translate-x-4 translate-y-4">🌲</div>
            <p class="text-xs text-stone-400 font-medium tracking-widest uppercase mb-2">Para ele</p>
            <h3 class="font-serif text-2xl font-bold text-white group-hover:text-stone-200 transition-colors">Masculino</h3>
            <p class="text-sm text-stone-400 mt-1">Amadeirados, Frescos, Aquáticos</p>
        </a>

        <a href="{{ route('products.index', ['gender' => 'unisex']) }}"
           class="group relative rounded-2xl overflow-hidden bg-gradient-to-br from-amber-100 to-orange-200 p-8 hover:shadow-lg transition-all duration-300 col-span-2 md:col-span-1">
            <div class="absolute bottom-0 right-0 text-8xl opacity-20 transform translate-x-4 translate-y-4">✨</div>
            <p class="text-xs text-amber-700 font-medium tracking-widest uppercase mb-2">Para todos</p>
            <h3 class="font-serif text-2xl font-bold text-amber-900 group-hover:text-amber-700 transition-colors">Unissex</h3>
            <p class="text-sm text-amber-800 mt-1">Nicho, Exclusivos, Únicos</p>
        </a>
    </div>
</section>

{{-- ============================================================
     PRODUTOS EM DESTAQUE
============================================================ --}}
<section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pb-16">
    <div class="flex items-center justify-between mb-8">
        <div>
            <p class="text-rose-500 text-sm font-medium tracking-widest uppercase">Curadoria Especial</p>
            <h2 class="font-serif text-3xl font-bold text-stone-900 mt-1">Destaques da Semana</h2>
        </div>
        <a href="{{ route('products.index') }}" class="text-sm text-rose-500 hover:text-rose-600 font-medium hidden sm:block">
            Ver todos →
        </a>
    </div>

    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        @foreach($featured as $product)
        <x-product-card :product="$product" />
        @endforeach
    </div>
</section>

{{-- ============================================================
     BANNER MEIO — PROMOÇÃO
============================================================ --}}
<section class="bg-rose-500 text-white py-12">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
        <p class="text-rose-200 text-sm font-medium tracking-widest uppercase mb-2">Oferta Especial</p>
        <h2 class="font-serif text-3xl font-bold mb-4">Use o cupom <span class="bg-white text-rose-600 px-3 py-1 rounded-lg">BEMVINDO10</span></h2>
        <p class="text-rose-100 mb-6">10% de desconto na sua primeira compra acima de R$ 100</p>
        <a href="{{ route('products.index') }}"
           class="bg-white text-rose-600 px-8 py-3 rounded-full font-semibold hover:bg-rose-50 transition-colors inline-block">
            Aproveitar agora
        </a>
    </div>
</section>

{{-- ============================================================
     LANÇAMENTOS
============================================================ --}}
<section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
    <div class="flex items-center justify-between mb-8">
        <div>
            <p class="text-rose-500 text-sm font-medium tracking-widest uppercase">Chegando Agora</p>
            <h2 class="font-serif text-3xl font-bold text-stone-900 mt-1">Lançamentos</h2>
        </div>
        <a href="{{ route('products.index', ['sort' => 'newest']) }}" class="text-sm text-rose-500 hover:text-rose-600 font-medium hidden sm:block">
            Ver todos →
        </a>
    </div>

    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        @foreach($newArrivals as $product)
        <x-product-card :product="$product" badge="Novo" />
        @endforeach
    </div>
</section>

{{-- ============================================================
     DIFERENCIAIS
============================================================ --}}
<section class="border-t border-stone-200 bg-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        <div class="grid grid-cols-2 md:grid-cols-4 gap-8 text-center">
            @php
                $features = [
                    ['icon' => '🚚', 'title' => 'Frete Grátis', 'desc' => 'Compras acima de R$ 299'],
                    ['icon' => '🔒', 'title' => 'Compra Segura', 'desc' => 'Pagamento criptografado'],
                    ['icon' => '↩️', 'title' => 'Troca Fácil', 'desc' => 'Até 30 dias após receber'],
                    ['icon' => '💬', 'title' => 'Suporte 24h', 'desc' => 'Chat e WhatsApp'],
                ];
            @endphp

            @foreach($features as $feature)
            <div class="p-4">
                <div class="text-3xl mb-3">{{ $feature['icon'] }}</div>
                <h3 class="font-semibold text-stone-900 mb-1">{{ $feature['title'] }}</h3>
                <p class="text-sm text-stone-500">{{ $feature['desc'] }}</p>
            </div>
            @endforeach
        </div>
    </div>
</section>

@endsection
