<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Api\V1\ApiController;
use App\Http\Requests\Category\CategoryRequest;
use App\Http\Resources\Category\CategoryResource;
use App\Models\Category;
use App\Services\CategoryService;
use Illuminate\Http\Request;

class CategoryController extends ApiController
{
    public function __construct(
        protected CategoryService $categoryService
    ) {}

    public function index(Request $request)
    {
        $categories = Category::query()
                    ->with('parent')
                    ->when(
                        $request->filled('search'),
                        fn ($q) => $q->where(
                            'name',
                            'like',
                            '%' . $request->search . '%'
                        )
                    )
                    ->when(
                        $request->filled('status'),
                        fn ($q) => $q->where(
                            'status',
                            $request->boolean('status')
                        )
                    )
                    ->orderBy('sort_order')
                    ->paginate(
                        $request->integer('per_page', 15)
                    );

        return $this->success([
            'categories' => CategoryResource::collection($categories),
            'pagination' => [
                'current_page' => $categories->currentPage(),
                'last_page' => $categories->lastPage(),
                'per_page' => $categories->perPage(),
                'total' => $categories->total(),
            ]
        ]);
    }

    public function store(CategoryRequest $request)
    {
        $category = $this->categoryService->create(
            $request->validated()
        );

        return $this->success([
            'category' => new CategoryResource($category)
        ], 'Category created successfully.', 201);
    }

    public function show(Category $category)
    {
        return $this->success([
            'category' => new CategoryResource($category)
        ]);
    }

    public function update(
        CategoryRequest $request,
        Category $category
    ) {
        $category = $this->categoryService->update(
            $category,
            $request->validated()
        );

        return $this->success([
            'category' => new CategoryResource($category)
        ], 'Category updated successfully.');
    }

    public function destroy(Category $category)
    {
        $category->delete();

        return $this->success(
            null,
            'Category deleted successfully.'
        );
    }
}
