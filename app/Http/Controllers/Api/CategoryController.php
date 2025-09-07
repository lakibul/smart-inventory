<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class CategoryController extends Controller
{
    /**
     * Display a listing of categories
     */
    public function index(Request $request)
    {
        try {
            $user = $request->user();

            if (!$user->can('category.view')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized access'
                ], 403);
            }

            $query = Category::with(['parent', 'children'])
                ->withCount('products');

            // Filter by search term
            if ($request->has('search')) {
                $search = $request->search;
                $query->where('name', 'like', "%{$search}%");
            }

            // Filter by level (0 = root categories)
            if ($request->has('level')) {
                $query->where('level', $request->level);
            }

            // Get tree structure or flat list
            if ($request->get('tree', false)) {
                $categories = $query->where('parent_id', null)->get();
                $this->loadChildrenRecursively($categories);
            } else {
                $categories = $query->orderBy('path')->get();
            }

            return response()->json([
                'success' => true,
                'data' => $categories
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get categories',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Store a newly created category
     */
    public function store(Request $request)
    {
        try {
            $user = $request->user();

            if (!$user->can('category.create')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized access'
                ], 403);
            }

            $request->validate([
                'name' => 'required|string|max:255',
                'parent_id' => 'nullable|exists:categories,id',
                'description' => 'nullable|string',
            ]);

            $slug = Str::slug($request->name);
            $level = 0;
            $path = $slug;

            // If has parent, calculate level and path
            if ($request->parent_id) {
                $parent = Category::findOrFail($request->parent_id);
                $level = $parent->level + 1;
                $path = $parent->path . '/' . $slug;
            }

            $category = Category::create([
                'name' => $request->name,
                'slug' => $slug,
                'parent_id' => $request->parent_id,
                'level' => $level,
                'path' => $path,
                'description' => $request->description,
            ]);

            $category->load('parent');

            return response()->json([
                'success' => true,
                'message' => 'Category created successfully',
                'data' => $category
            ], 201);

        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create category',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified category
     */
    public function show(Request $request, Category $category)
    {
        try {
            $user = $request->user();

            if (!$user->can('category.view')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized access'
                ], 403);
            }

            $category->load(['parent', 'children', 'products' => function ($query) {
                $query->where('status', 'active');
            }]);

            return response()->json([
                'success' => true,
                'data' => $category
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get category',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update the specified category
     */
    public function update(Request $request, Category $category)
    {
        try {
            $user = $request->user();

            if (!$user->can('category.edit')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized access'
                ], 403);
            }

            $request->validate([
                'name' => 'sometimes|required|string|max:255',
                'parent_id' => 'nullable|exists:categories,id',
                'description' => 'nullable|string',
            ]);

            // Check if trying to set parent to itself or its descendant
            if ($request->parent_id && $this->isDescendant($category->id, $request->parent_id)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot set parent to self or descendant'
                ], 422);
            }

            $data = $request->only(['name', 'parent_id', 'description']);

            // Update slug if name changed
            if ($request->has('name')) {
                $data['slug'] = Str::slug($request->name);
            }

            // Recalculate level and path if parent changed or name changed
            if ($request->has('parent_id') || $request->has('name')) {
                if ($request->parent_id) {
                    $parent = Category::findOrFail($request->parent_id);
                    $data['level'] = $parent->level + 1;
                    $data['path'] = $parent->path . '/' . ($data['slug'] ?? $category->slug);
                } else {
                    $data['level'] = 0;
                    $data['path'] = $data['slug'] ?? $category->slug;
                }
            }

            $category->update($data);

            // Update all descendants' paths if necessary
            if (isset($data['path'])) {
                $this->updateDescendantsPaths($category);
            }

            $category->load('parent');

            return response()->json([
                'success' => true,
                'message' => 'Category updated successfully',
                'data' => $category
            ]);

        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update category',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified category
     */
    public function destroy(Request $request, Category $category)
    {
        try {
            $user = $request->user();

            if (!$user->can('category.delete')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized access'
                ], 403);
            }

            // Check if category has products
            if ($category->products()->count() > 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot delete category with products'
                ], 422);
            }

            // Check if category has children
            if ($category->children()->count() > 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot delete category with subcategories'
                ], 422);
            }

            $category->delete();

            return response()->json([
                'success' => true,
                'message' => 'Category deleted successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete category',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Load children recursively for tree structure
     */
    private function loadChildrenRecursively($categories)
    {
        foreach ($categories as $category) {
            $category->load(['children' => function ($query) {
                $query->withCount('products');
            }]);

            if ($category->children->isNotEmpty()) {
                $this->loadChildrenRecursively($category->children);
            }
        }
    }

    /**
     * Check if a category is descendant of another
     */
    private function isDescendant($categoryId, $parentId)
    {
        $parent = Category::find($parentId);

        while ($parent) {
            if ($parent->id == $categoryId) {
                return true;
            }
            $parent = $parent->parent;
        }

        return false;
    }

    /**
     * Update paths for all descendants
     */
    private function updateDescendantsPaths(Category $category)
    {
        $descendants = Category::where('parent_id', $category->id)->get();

        foreach ($descendants as $descendant) {
            $descendant->update([
                'level' => $category->level + 1,
                'path' => $category->path . '/' . $descendant->slug
            ]);

            $this->updateDescendantsPaths($descendant);
        }
    }
}
