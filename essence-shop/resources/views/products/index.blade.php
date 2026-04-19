@extends('layouts.app')

@section('title', 'Catálogo de Perfumes')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

    {{-- Breadcrumb --}}
    <nav class="text-sm text-stone-500 mb-6">
        <a href="{{ route('home') }}" class="hover:text-rose-500">Home</a>
        <span class="mx-2">/</span>
        <span class="text-stone-900">Produtos</span>
        @if(request('gender'))
        <span class="mx-2">/</span>
        <span class="text-stone-900">{{ ucfirst(request('gender') === 'female' ? 'Feminino' : (request('gender') === 'male' ? 'Masculino' : 'Unissex')) }}</span>
        @endif
    </nav>

    <div class="flex gap-8" x-data="{ filtersOpen: false }">

        {{-- ============================================================
             SIDEBAR DE FILTROS
        ============================================================ --}}
        <aside class="w-56 flex-shrink-0 hidden lg:block">
            <div class="sticky top-24">

                {{-- Gênero --}}
                <div class="mb-6">
                    <h4 class="font-semibold text-stone-900 text-sm mb-3">Gênero</h4>
                    <div class="space-y-2 text-sm">
                        @foreach(['female' => 'Feminino', 'male' => 'Masculino', 'unisex' => 'Unissex'] as $val => $label)
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="radio" name="gender" value="{{ $val }}" form="filters-form"
                                {{ request('gender') === $val ? 'checked' : '' }}
                                class="accent-rose-500">
                            <span class="text-stone-700">{{ $label }}</span>
                        </label>
                        @endforeach
                    </div>
                </div>

                {{-- Categorias --}}
                <div class="mb-6">
                    <h4 class="font-semibold text-stone-900 text-sm mb-3">Categoria</h4>
                    <div class="space-y-2 text-sm">
                        @foreach($categories as $cat)
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="radio" name="category" value="{{ $cat->slug }}" form="filters-form"
                                {{ request('category') === $cat->slug ? 'checked' : '' }}
                                class="accent-rose-500">
                            <span class="text-stone-700">{{ $cat->name }}</span>
                        </label>
                        @endforeach
                    </div>
                </div>

                {{-- Marcas --}}
                <div class="mb-6">
                    <h4 class="font-semibold text-stone-900 text-sm mb-3">Marca</h4>
                    <div class="space-y-2 text-sm max-h-48 overflow-y-auto">
                        @foreach($brands as $brand)
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="checkbox" name="brand" value="{{ $brand->slug }}" form="filters-form"
                                {{ request('brand') === $brand->slug ? 'checked' : '' }}
                                class="accent-rose-500">
                            <span class="text-stone-700">{{ $brand->name }}</span>
                        </label>
                        @endforeach
                    </div>
                </div>

                {{-- Faixa de Preço --}}
                <div class="mb-6">
                    <h4 class="font-semibold text-stone-900 text-sm mb-3">Preço</h4>
                    <div class="flex items-center gap-2 text-sm">
                        <input type="number" name="price_min" form="filters-form" placeholder="Min"
                            value="{{ request('price_min') }}"
                            class="w-20 border border-stone-300 rounded-lg px-2 py-1.5 text-center focus:outline-none focus:border-rose-400">
                        <span class="text-stone-400">—</span>
                        <input type="number" name="price_max" form="filters-form" placeholder="Max"
                            value="{{ request('price_max') }}"
                            class="w-20 border border-stone-300 rounded-lg px-2 py-1.5 text-center focus:outline-none focus:border-rose-400">
                    </div>
                </div>

                <form id="filters-form" method="GET" action="{{ route('products.index') }}">
                    @if(request('q'))
                        <input type="hidden" name="q" value="{{ request('q') }}">
                    @endif
                    <button type="submit" class="w-full bg-rose-500 text-white py-2 rounded-full text-sm font-medium hover:bg-rose-600 transition-colors">
                        Filtrar
                    </button>
                    @if(request()->hasAny(['gender','category','brand','price_min','price_max']))
                    <a href="{{ route('products.index') }}" class="block w-full text-center text-stone-500 text-xs mt-3 hover:text-rose-500">
                        Limpar filtros
                    </a>
                    @endif
                </form>
            </div>
        </aside>

        {{-- ============================================================
             LISTAGEM DE PRODUTOS
        ============================================================ --}}
        <div class="flex-1 min-w-0">

            {{-- Header com contagem e ordenação --}}
            <div class="flex items-center justify-between mb-6">
                <p class="text-sm text-stone-500">
                    <span class="font-medium text-stone-900">{{ $products->total() }}</span> produtos encontrados
                </p>
                <div class="flex items-center gap-3">
                    <label class="text-sm text-stone-500 hidden sm:block">Ordenar por</label>
                    <select onchange="window.location = this.value" class="border border-stone-300 rounded-lg px-3 py-1.5 text-sm focus:outline-none focus:border-rose-400 bg-white">
                        @php
                            $sorts = ['relevancia' => 'Relevância', 'newest' => 'Mais novos', 'price_asc' => 'Menor preço', 'price_desc' => 'Maior preço', 'rating' => 'Melhor avaliado'];
                            $currentSort = request('sort', 'relevancia');
                        @endphp
                        @foreach($sorts as $val => $label)
                        <option value="{{ route('products.index', array_merge(request()->query(), ['sort' => $val])) }}"
                            {{ $currentSort === $val ? 'selected' : '' }}>
                            {{ $label }}
                        </option>
                        @endforeach
                    </select>
                </div>
            </div>

            {{-- Grid de Produtos --}}
            @if($products->isEmpty())
            <div class="text-center py-20">
                <div class="text-4xl mb-4">🔍</div>
                <h3 class="font-serif text-xl font-semibold text-stone-700 mb-2">Nenhum produto encontrado</h3>
                <p class="text-stone-500 text-sm">Tente ajustar seus filtros ou faça uma nova busca.</p>
                <a href="{{ route('products.index') }}" class="mt-4 inline-block text-rose-500 hover:text-rose-600 text-sm font-medium">
                    Ver todos os produtos
                </a>
            </div>
            @else
            <div class="grid grid-cols-2 md:grid-cols-3 xl:grid-cols-4 gap-4">
                @foreach($products as $product)
                <x-product-card :product="$product" />
                @endforeach
            </div>

            {{-- Paginação --}}
            <div class="mt-10">
                {{ $products->links() }}
            </div>
            @endif
        </div>
    </div>
</div>
@endsection
