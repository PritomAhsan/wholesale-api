<?php

namespace App\Services;

use App\Models\Product;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ProductService
{
    public function create(array $data): Product
    {
        return DB::transaction(function () use ($data) {
            $data['slug'] ??= Str::slug($data['name']);
            $data['sku'] ??= strtoupper('PRD-'.Str::random(8));
            $product = Product::create($data);

            if (! empty($data['category_ids'])) {
                $product->categories()->sync(
                    $data['category_ids']
                );
            }

            if (! empty($data['attributes'])) {
                foreach ($data['attributes'] as $attribute) {
                    $product
                        ->assignedAttributes()
                        ->create([
                            'attribute_id' => $attribute['attribute_id'],
                            'attribute_value_id' => $attribute['attribute_value_id'],
                        ]);

                }

            }
        });
    }

    public function update(Product $product, array $data): Product
    {
        return DB::transaction(function () use ($product,$data) {
            if (!empty($data['name']) && empty($data['slug'])) $data['slug']=Str::slug($data['name']);
            $product->update($data);
            if (array_key_exists(
                'category_ids',
                $data
            )) {
                $product->categories()->sync(
                    $data['category_ids']
                );
            }
            $product
                ->assignedAttributes()
                ->delete();
            if (! empty($data['attributes'])) {
                foreach ($data['attributes'] as $attribute) {
                    $product
                        ->assignedAttributes()
                        ->create([
                            'attribute_id' => $attribute['attribute_id'],
                            'attribute_value_id' => $attribute['attribute_value_id'],
                        ]);

                }

            }
            return $product->fresh(['supplier','brand','unit']);
        });
    }

    public function delete(Product $product): void { $product->delete(); }
    public function restore(string $uuid): Product { $p=Product::withTrashed()->where('uuid',$uuid)->firstOrFail(); $p->restore(); return $p; }
    public function forceDelete(string $uuid): void { Product::withTrashed()->where('uuid',$uuid)->firstOrFail()->forceDelete(); }
    public function publish(Product $product): Product { $product->update(['status'=>'published','published_at'=>now()]); return $product->fresh(); }
    public function approve(Product $product,int $userId): Product { $product->update(['status'=>'approved','approved_at'=>now(),'approved_by'=>$userId]); return $product->fresh(); }
    public function reject(Product $product): Product { $product->update(['status'=>'rejected']); return $product->fresh(); }
    public function archive(Product $product): Product { $product->update(['status'=>'archived']); return $product->fresh(); }
    public function toggleFeatured(Product $product): Product { $product->update(['featured'=>!$product->featured]); return $product->fresh(); }
    public function updateStock(Product $product,int $quantity): Product { $product->update(['stock_quantity'=>$quantity]); return $product->fresh(); }
}
