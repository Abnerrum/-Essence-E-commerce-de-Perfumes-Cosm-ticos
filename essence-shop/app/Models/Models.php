<?php
// ============================================================
// Model: Product.php
// ============================================================
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;

class Product extends Model
{
    use HasFactory, SoftDeletes, HasSlug;

    protected $fillable = [
        'category_id', 'brand_id', 'name', 'slug', 'description',
        'notes', 'gender', 'price', 'price_sale', 'sku', 'stock',
        'volume', 'concentration', 'active', 'featured', 'new_arrival',
        'rating_avg', 'rating_count',
    ];

    protected $casts = [
        'price'       => 'decimal:2',
        'price_sale'  => 'decimal:2',
        'active'      => 'boolean',
        'featured'    => 'boolean',
        'new_arrival' => 'boolean',
        'rating_avg'  => 'decimal:2',
    ];

    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()->generateSlugsFrom('name')->saveSlugsTo('slug');
    }

    // Relationships
    public function category(): BelongsTo { return $this->belongsTo(Category::class); }
    public function brand(): BelongsTo    { return $this->belongsTo(Brand::class); }
    public function images(): HasMany     { return $this->hasMany(ProductImage::class)->orderBy('sort_order'); }
    public function reviews(): HasMany    { return $this->hasMany(Review::class)->where('approved', true); }

    // Accessors
    public function getCurrentPriceAttribute(): float
    {
        return $this->price_sale ?? $this->price;
    }

    public function getIsOnSaleAttribute(): bool
    {
        return !is_null($this->price_sale) && $this->price_sale < $this->price;
    }

    public function getDiscountPercentAttribute(): int
    {
        if (!$this->is_on_sale) return 0;
        return (int) round((1 - $this->price_sale / $this->price) * 100);
    }

    public function getPrimaryImageAttribute(): ?ProductImage
    {
        return $this->images->firstWhere('is_primary', true) ?? $this->images->first();
    }

    // Scopes
    public function scopeActive($query)        { return $query->where('active', true); }
    public function scopeFeatured($query)      { return $query->where('featured', true); }
    public function scopeNewArrivals($query)   { return $query->where('new_arrival', true); }
    public function scopeForGender($query, $gender) {
        return $query->whereIn('gender', [$gender, 'unisex']);
    }
    public function scopeInStock($query)       { return $query->where('stock', '>', 0); }
}

// ============================================================
// Model: Category.php
// ============================================================
class Category extends Model
{
    use HasFactory, HasSlug;

    protected $fillable = ['name', 'slug', 'gender', 'description', 'image', 'active', 'sort_order'];

    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()->generateSlugsFrom('name')->saveSlugsTo('slug');
    }

    public function products(): HasMany { return $this->hasMany(Product::class); }
}

// ============================================================
// Model: Brand.php
// ============================================================
class Brand extends Model
{
    use HasFactory, HasSlug;

    protected $fillable = ['name', 'slug', 'description', 'logo', 'active'];

    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()->generateSlugsFrom('name')->saveSlugsTo('slug');
    }

    public function products(): HasMany { return $this->hasMany(Product::class); }
}

// ============================================================
// Model: Order.php
// ============================================================
class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_number', 'user_id', 'status', 'subtotal', 'shipping_cost',
        'discount', 'total', 'shipping_zip', 'shipping_street', 'shipping_number',
        'shipping_complement', 'shipping_neighborhood', 'shipping_city',
        'shipping_state', 'payment_method', 'payment_status', 'payment_id',
        'shipping_service', 'tracking_code', 'notes',
    ];

    protected static function booted(): void
    {
        static::creating(function (Order $order) {
            $order->order_number = 'ESS-' . strtoupper(uniqid());
        });
    }

    public function user(): BelongsTo    { return $this->belongsTo(User::class); }
    public function items(): HasMany     { return $this->hasMany(OrderItem::class); }

    public function getStatusLabelAttribute(): string
    {
        return match($this->status) {
            'pending'    => 'Aguardando pagamento',
            'confirmed'  => 'Confirmado',
            'processing' => 'Em separação',
            'shipped'    => 'Enviado',
            'delivered'  => 'Entregue',
            'cancelled'  => 'Cancelado',
            'refunded'   => 'Reembolsado',
            default      => $this->status,
        };
    }

    public function getStatusColorAttribute(): string
    {
        return match($this->status) {
            'pending'    => 'yellow',
            'confirmed'  => 'blue',
            'processing' => 'indigo',
            'shipped'    => 'purple',
            'delivered'  => 'green',
            'cancelled'  => 'red',
            'refunded'   => 'gray',
            default      => 'gray',
        };
    }
}

// ============================================================
// Model: Cart.php
// ============================================================
class Cart extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'session_id'];

    public function items(): HasMany { return $this->hasMany(CartItem::class); }

    public function getTotalAttribute(): float
    {
        return $this->items->sum(fn($item) => $item->quantity * $item->unit_price);
    }

    public function getItemsCountAttribute(): int
    {
        return $this->items->sum('quantity');
    }
}

// ============================================================
// Model: Review.php
// ============================================================
class Review extends Model
{
    use HasFactory;

    protected $fillable = ['product_id', 'user_id', 'rating', 'title', 'body', 'approved'];

    public function product(): BelongsTo { return $this->belongsTo(Product::class); }
    public function user(): BelongsTo   { return $this->belongsTo(User::class); }
}
