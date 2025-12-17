<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductsAttribute extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'size',
        'price',
        'stock',
        'sku',
        'status'
    ];


    public static function getProductStock($product_id, $size)
    {
        // Aggregate total stock for the product across all attribute rows
        // (size is currently unused in the storefront; if size-level stock is needed later, filter by size)
        return (int) ProductsAttribute::where('product_id', $product_id)->sum('stock');
    }


    // Note: We need to prevent orders (upon checkout and payment) of the 'disabled' products (`status` = 0), where the product ITSELF can be disabled in admin/products/products.blade.php (by checking the `products` database table) or a product's attribute (`stock`) can be disabled in 'admin/attributes/add_edit_attributes.blade.php' (by checking the `products_attributes` database table). We also prevent orders of the out of stock / sold-out products (by checking the `products_attributes` database table)
    public static function getAttributeStatus($product_id, $size)
    {
        $getAttributeStatus = ProductsAttribute::select('status')->where([
            'product_id' => $product_id,
            // 'size'       => $size
        ])->first();

        return $getAttributeStatus->status ?? null;
    }
}
