<?php

use App\Http\Controllers\ProductController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\Admin;
use Illuminate\Support\Facades\Route;

// ─────────────────────────────────────────────
// Rotas públicas
// ─────────────────────────────────────────────

Route::get('/', function () {
    $featured    = \App\Models\Product::with(['images'])->active()->featured()->limit(8)->get();
    $newArrivals = \App\Models\Product::with(['images'])->active()->newArrivals()->limit(8)->get();
    $categories  = \App\Models\Category::where('active', true)->orderBy('sort_order')->get();

    return view('home', compact('featured', 'newArrivals', 'categories'));
})->name('home');

// Catálogo de produtos
Route::get('/produtos',               [ProductController::class, 'index'])->name('products.index');
Route::get('/produtos/{slug}',        [ProductController::class, 'show'])->name('products.show');
Route::get('/busca',                  [ProductController::class, 'search'])->name('products.search');

// Carrinho (funciona para visitantes e logados)
Route::prefix('carrinho')->name('cart.')->group(function () {
    Route::get('/',                   [CartController::class, 'index'])->name('index');
    Route::post('/adicionar',         [CartController::class, 'add'])->name('add');
    Route::patch('/atualizar/{item}', [CartController::class, 'update'])->name('update');
    Route::delete('/remover/{item}',  [CartController::class, 'remove'])->name('remove');
    Route::post('/cupom',             [CartController::class, 'applyCoupon'])->name('coupon');
});

// ─────────────────────────────────────────────
// Rotas autenticadas (cliente)
// ─────────────────────────────────────────────

Route::middleware(['auth', 'verified'])->group(function () {

    // Checkout
    Route::prefix('checkout')->name('checkout.')->group(function () {
        Route::get('/',                     [CheckoutController::class, 'index'])->name('index');
        Route::post('/frete',               [CheckoutController::class, 'shipping'])->name('shipping');
        Route::get('/pagamento',            [CheckoutController::class, 'payment'])->name('payment');
        Route::post('/finalizar',           [CheckoutController::class, 'process'])->name('process');
    });

    // Pedidos
    Route::prefix('meus-pedidos')->name('orders.')->group(function () {
        Route::get('/',                     [OrderController::class, 'index'])->name('index');
        Route::get('/{order}',              [OrderController::class, 'show'])->name('show');
    });

    // Perfil e endereços
    Route::prefix('minha-conta')->name('account.')->group(function () {
        Route::get('/',                     [\App\Http\Controllers\AccountController::class, 'index'])->name('index');
        Route::put('/perfil',               [\App\Http\Controllers\AccountController::class, 'update'])->name('update');

        Route::prefix('enderecos')->name('addresses.')->group(function () {
            Route::get('/',                 [\App\Http\Controllers\AddressController::class, 'index'])->name('index');
            Route::get('/novo',             [\App\Http\Controllers\AddressController::class, 'create'])->name('create');
            Route::post('/',               [\App\Http\Controllers\AddressController::class, 'store'])->name('store');
            Route::get('/{address}/editar',[\App\Http\Controllers\AddressController::class, 'edit'])->name('edit');
            Route::put('/{address}',        [\App\Http\Controllers\AddressController::class, 'update'])->name('update');
            Route::delete('/{address}',     [\App\Http\Controllers\AddressController::class, 'destroy'])->name('destroy');
        });
    });

    // Lista de desejos
    Route::post('/favoritos/{product}',   [\App\Http\Controllers\WishlistController::class, 'toggle'])->name('wishlist.toggle');
    Route::get('/favoritos',              [\App\Http\Controllers\WishlistController::class, 'index'])->name('wishlist.index');

    // Avaliações
    Route::post('/produtos/{product}/avaliar', [\App\Http\Controllers\ReviewController::class, 'store'])->name('reviews.store');
});

// ─────────────────────────────────────────────
// Painel Administrativo
// ─────────────────────────────────────────────

Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {

    Route::get('/', fn() => redirect()->route('admin.dashboard'))->name('index');
    Route::get('/dashboard', [\App\Http\Controllers\Admin\DashboardController::class, 'index'])->name('dashboard');

    // Produtos
    Route::resource('products',   \App\Http\Controllers\Admin\ProductController::class);

    // Categorias
    Route::resource('categories', \App\Http\Controllers\Admin\CategoryController::class);

    // Marcas
    Route::resource('brands',     \App\Http\Controllers\Admin\BrandController::class);

    // Pedidos
    Route::get('/orders',                                    [\App\Http\Controllers\Admin\OrderController::class, 'index'])->name('orders.index');
    Route::get('/orders/{order}',                            [\App\Http\Controllers\Admin\OrderController::class, 'show'])->name('orders.show');
    Route::patch('/orders/{order}/status',                   [\App\Http\Controllers\Admin\OrderController::class, 'updateStatus'])->name('orders.status');

    // Usuários
    Route::get('/users',                                     [\App\Http\Controllers\Admin\UserController::class, 'index'])->name('users.index');

    // Avaliações
    Route::get('/reviews',                                   [\App\Http\Controllers\Admin\ReviewController::class, 'index'])->name('reviews.index');
    Route::patch('/reviews/{review}/approve',                [\App\Http\Controllers\Admin\ReviewController::class, 'approve'])->name('reviews.approve');
    Route::delete('/reviews/{review}',                       [\App\Http\Controllers\Admin\ReviewController::class, 'destroy'])->name('reviews.destroy');

    // Cupons
    Route::resource('coupons',    \App\Http\Controllers\Admin\CouponController::class);
});

// ─────────────────────────────────────────────
// Auth (geradas pelo Laravel Breeze)
// ─────────────────────────────────────────────

require __DIR__ . '/auth.php';

// ─────────────────────────────────────────────
// OAuth Google (Socialite)
// ─────────────────────────────────────────────

Route::get('/auth/google',           [\App\Http\Controllers\Auth\SocialiteController::class, 'redirect'])->name('auth.google');
Route::get('/auth/google/callback',  [\App\Http\Controllers\Auth\SocialiteController::class, 'callback'])->name('auth.google.callback');

// ─────────────────────────────────────────────
// Webhook Mercado Pago
// ─────────────────────────────────────────────

Route::post('/webhook/mercadopago',  [\App\Http\Controllers\WebhookController::class, 'mercadopago'])
    ->name('webhook.mercadopago')
    ->withoutMiddleware([\App\Http\Middleware\VerifyCsrfToken::class]);
