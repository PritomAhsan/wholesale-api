<?php

namespace App\Http\Controllers\Api\V1\Product;

use App\Enums\ProductStatus;
use App\Http\Controllers\Api\V1\ApiController;
use App\Http\Resources\Category\CategoryResource;
use App\Models\Category;

class PublicCategoryController extends ApiController
{
    public function index()
    {
        $categories = Category::whereNull('parent_id')
            ->where('status', true)
            ->with([
                'children' => function ($query) {
                    $query->where('status', true)
                          ->orderBy('sort_order');
                }
            ])
            ->withCount([
                'products' => fn ($query) => $query->where(
                    'status',
                    ProductStatus::PUBLISHED
                ),
            ])
            ->orderBy('sort_order')
            ->get();

        return $this->success([
            'categories' => CategoryResource::collection($categories)
        ]);
    }

    public function show(Category $category)
    {
        $category->loadCount([
            'products' => fn ($query) => $query->where(
                'status',
                ProductStatus::PUBLISHED
            ),
        ]);

        return $this->success([
            'category' => new CategoryResource(
                $category->load('children')
            )
        ]);
    }
}
