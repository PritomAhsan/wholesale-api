<?php

namespace Database\Seeders;

use App\Enums\ProductStatus;
use App\Models\Attribute;
use App\Models\AttributeValue;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductApproval;
use App\Models\ProductAssignedAttribute;
use App\Models\ProductImage;
use App\Models\ProductStatusHistory;
use App\Models\ProductVariant;
use App\Models\ProductVariantAttributeValue;
use App\Models\Supplier;
use App\Models\Unit;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class DemoProductSeeder extends Seeder
{
    /** @var Product[] */
    public array $products = [];

    private const NAME_TEMPLATES = [
        'Stainless Steel {noun}', 'Industrial Grade {noun}', 'Heavy-Duty {noun}',
        'Eco-Friendly {noun}', 'Premium {noun} Set', 'Wholesale {noun}',
        'Commercial {noun}', 'Multi-Purpose {noun}', 'Compact {noun}',
        'Professional {noun} Kit', 'Bulk Pack {noun}', 'Deluxe {noun}',
    ];

    private const NOUNS = [
        'Bluetooth Speaker', 'USB-C Cable', 'Power Bank', 'LED Desk Lamp', 'Smart Plug',
        'Cordless Drill', 'Wrench Set', 'Safety Helmet', 'Work Gloves', 'Bolt & Nut Kit',
        'Cotton T-Shirt', 'Denim Jacket', 'Canvas Tote Bag', 'Running Shoes', 'Fleece Blanket',
        'Non-Stick Pan', 'Storage Container', 'Office Chair', 'Garden Hose', 'Cleaning Spray Bottle',
        'Corrugated Box', 'Shipping Label Roll', 'Bubble Wrap Roll', 'Gift Ribbon Spool',
        'Notebook Pack', 'Ballpoint Pen Box', 'Desk Organizer', 'Ink Cartridge Set',
        'Face Moisturizer', 'Shampoo Bottle', 'Hand Sanitizer Pack', 'Bar Soap Case',
        'Snack Variety Box', 'Bottled Water Case', 'Coffee Bean Sack', 'Rice Bag',
    ];

    public function run(): void
    {
        $suppliers = Supplier::where('status', 'approved')->get();
        $brands = Brand::pluck('id')->all();
        $units = Unit::pluck('id')->all();
        $leafCategories = Category::whereNotNull('parent_id')->get();
        $colorAttr = Attribute::where('name', 'Color')->first();
        $sizeAttr = Attribute::where('name', 'Size')->first();
        $colorValues = $colorAttr ? AttributeValue::where('attribute_id', $colorAttr->id)->get() : collect();
        $sizeValues = $sizeAttr ? AttributeValue::where('attribute_id', $sizeAttr->id)->get() : collect();

        $admin = \App\Models\User::role('Super Admin')->first();

        $statusMix = array_merge(
            array_fill(0, 34, ProductStatus::PUBLISHED),
            array_fill(0, 4, ProductStatus::PENDING),
            array_fill(0, 3, ProductStatus::APPROVED),
            array_fill(0, 3, ProductStatus::REJECTED),
            array_fill(0, 3, ProductStatus::DRAFT),
            array_fill(0, 1, ProductStatus::ARCHIVED),
        );
        shuffle($statusMix);

        $count = count(self::NOUNS);
        $productIndex = 0;

        foreach (self::NOUNS as $i => $noun) {
            $template = self::NAME_TEMPLATES[$i % count(self::NAME_TEMPLATES)];
            $name = str_replace('{noun}', $noun, $template);
            $supplier = $suppliers[$i % $suppliers->count()];
            $status = $statusMix[$productIndex] ?? ProductStatus::PUBLISHED;
            $productIndex++;

            $costPrice = round(mt_rand(300, 4000) / 100, 2);
            $sellingPrice = round($costPrice * (mt_rand(130, 180) / 100), 2);
            $compareAt = mt_rand(0, 100) < 40 ? round($sellingPrice * (mt_rand(115, 140) / 100), 2) : null;

            $publishedAt = in_array($status, [ProductStatus::PUBLISHED, ProductStatus::ARCHIVED], true)
                ? now()->subDays(rand(1, 180))
                : null;
            $approvedAt = in_array($status, [ProductStatus::PUBLISHED, ProductStatus::APPROVED, ProductStatus::ARCHIVED], true)
                ? now()->subDays(rand(1, 200))
                : null;

            $product = Product::updateOrCreate(
                ['slug' => Str::slug($name) . '-' . $supplier->id],
                [
                    'supplier_id' => $supplier->id,
                    'brand_id' => $brands[array_rand($brands)],
                    'unit_id' => $units[array_rand($units)],
                    'name' => $name,
                    'sku' => 'BLK-' . strtoupper(Str::random(8)),
                    'short_description' => "Bulk-ready {$noun} for wholesale buyers — verified quality, fast dispatch.",
                    'description' => "This {$noun} is sourced directly from {$supplier->company_name}, a verified {$supplier->business_type} on Bulkare. Built for wholesale volume with consistent quality control across every batch. Minimum order quantities and tiered bulk pricing apply — contact the seller for custom quotes on large orders.\n\nKey features:\n- Quality-inspected before dispatch\n- Consistent batch-to-batch specifications\n- Flexible packaging for bulk shipment\n- Backed by Bulkare's verified seller program",
                    'cost_price' => $costPrice,
                    'selling_price' => $sellingPrice,
                    'compare_at_price' => $compareAt,
                    'currency' => 'USD',
                    'min_order_quantity' => [1, 5, 10, 25, 50][array_rand([1, 5, 10, 25, 50])],
                    'stock_quantity' => $status === ProductStatus::ARCHIVED ? 0 : rand(0, 2000),
                    'featured' => mt_rand(0, 100) < 20,
                    'status' => $status,
                    'approved_at' => $approvedAt,
                    'approved_by' => $approvedAt ? $admin?->id : null,
                    'published_at' => $publishedAt,
                ]
            );

            // Categories — 1–2 leaf categories per product.
            $product->categories()->sync(
                $leafCategories->random(min(2, $leafCategories->count()))->pluck('id')
            );

            // Images — 3 per product, first one primary.
            $product->images()->delete();
            for ($img = 0; $img < 3; $img++) {
                ProductImage::create([
                    'product_id' => $product->id,
                    'image' => 'https://picsum.photos/seed/bulkare-product-' . ((($i * 3 + $img) % 48) + 1) . '/800/800',
                    'alt_text' => $name,
                    'is_primary' => $img === 0,
                    'sort_order' => $img,
                ]);
            }

            // Assigned attributes + 2 variants (color x nothing, kept simple) when values exist.
            if ($colorValues->isNotEmpty()) {
                $colorValue = $colorValues->random();
                ProductAssignedAttribute::updateOrCreate(
                    ['product_id' => $product->id, 'attribute_id' => $colorAttr->id],
                    ['attribute_value_id' => $colorValue->id]
                );
            }

            if ($colorValues->isNotEmpty() && mt_rand(0, 100) < 60) {
                $product->variants()->delete();
                $variantColors = $colorValues->random(min(2, $colorValues->count()));
                foreach ($variantColors as $vi => $colorValue) {
                    $variant = ProductVariant::create([
                        'product_id' => $product->id,
                        'sku' => $product->sku . '-V' . ($vi + 1),
                        'cost_price' => $costPrice,
                        'selling_price' => $sellingPrice,
                        'compare_at_price' => $compareAt,
                        'stock_quantity' => rand(0, 500),
                        'low_stock_quantity' => 10,
                        'is_active' => true,
                        'is_default' => $vi === 0,
                        'sort_order' => $vi,
                        'wholesale_price' => round($sellingPrice * 0.85, 2),
                        'minimum_order_quantity' => $product->min_order_quantity,
                    ]);

                    ProductVariantAttributeValue::create([
                        'product_variant_id' => $variant->id,
                        'attribute_id' => $colorAttr->id,
                        'attribute_value_id' => $colorValue->id,
                    ]);
                }
            }

            // Status history + approval trail so the admin approvals
            // screen has real records to show, not just current status.
            ProductStatusHistory::create([
                'product_id' => $product->id,
                'user_id' => $supplier->user_id,
                'action' => 'create',
                'status_before' => 'draft',
                'status_after' => 'draft',
                'remarks' => 'Product created.',
                'performed_at' => now()->subDays(rand(180, 220)),
            ]);

            if ($status !== ProductStatus::DRAFT) {
                ProductApproval::create([
                    'product_id' => $product->id,
                    'reviewer_id' => $status === ProductStatus::PENDING ? null : $admin?->id,
                    'action' => 'submit',
                    'decision' => match ($status) {
                        ProductStatus::REJECTED => 'rejected',
                        ProductStatus::PENDING => null,
                        default => 'approved',
                    },
                    'status_before' => 'draft',
                    'status_after' => $status === ProductStatus::PENDING ? 'pending' : $status->value,
                    'remarks' => $status === ProductStatus::REJECTED
                        ? 'Product images do not clearly show packaging — please resubmit with clearer photos and confirm certification documents.'
                        : ($status === ProductStatus::PENDING ? null : 'Meets listing quality guidelines.'),
                    'reviewed_at' => $status === ProductStatus::PENDING ? null : now()->subDays(rand(1, 150)),
                ]);
            }

            $this->products[] = $product;
        }
    }
}
