<?php

namespace App\Services;

use App\Models\Category;
use Illuminate\Support\Str;
use App\Services\MediaService;

class CategoryService
{
    public function __construct(
        protected MediaService $mediaService
    ) {}

    public function create(array $data): Category
    {
        $image = null;

        if (request()->hasFile('image')) {
            $image = $this->mediaService->upload(
                request()->file('image'),
                'categories'
            );
        }

        $icon = null;

        if (request()->hasFile('icon')) {
            $icon = $this->mediaService->upload(
                request()->file('icon'),
                'categories/icons'
            );
        }

        return Category::create([

            'parent_id' => $data['parent_id'] ?? null,

            'name' => $data['name'],

            'slug' => Str::slug($data['name']),

            'description' => $data['description'] ?? null,

            'image' => $image ?? NULL,

            'icon' => $icon ?? NULL,

            'sort_order' => $data['sort_order'] ?? 0,

            'status' => $data['status'] ?? true,

        ]);
    }

    public function update(Category $category, array $data): Category
    {
        $image = $category->image;

        if (request()->hasFile('image')) {

            $image = $this->mediaService->replace(

                request()->file('image'),

                $category->image,

                'categories'
            );
        }

        $icon = $category->icon;

        if (request()->hasFile('icon')) {

            $icon = $this->mediaService->replace(

                request()->file('icon'),

                $category->icon,

                'categories'
            );
        }

        $category->update([

            'parent_id' => $data['parent_id'] ?? null,

            'name' => $data['name'],

            'slug' => Str::slug($data['name']),

            'description' => $data['description'] ?? null,

            'image' => $image ?? NULL,

            'icon' => $icon ?? NULL,

            'sort_order' => $data['sort_order'] ?? 0,

            'status' => $data['status'] ?? true,

        ]);

        return $category->fresh();
    }
}
