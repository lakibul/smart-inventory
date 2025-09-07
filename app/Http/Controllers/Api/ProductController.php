<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\StockLevel;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class ProductController extends Controller
{
    /**
     * Display a listing of products
     */
    public function index(Request $request)
    {
        try {
            $user = $request->user();

            if (!$user->can('product.view')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized access'
                ], 403);
            }

            $query = Product::with(['category', 'stockLevels.warehouse']);

            // Filter by search term
            if ($request->has('search')) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('sku', 'like', "%{$search}%")
                      ->orWhere('barcode', 'like', "%{$search}%");
                });
            }

            // Filter by category
            if ($request->has('category_id')) {
                $query->where('category_id', $request->category_id);
            }

            // Filter by status
            if ($request->has('status')) {
                $query->where('status', $request->status);
            } else {
                $query->where('status', 'active');
            }

            // Filter by warehouse (if user is assigned to specific warehouse)
            if ($user->warehouse_id) {
                $query->whereHas('stockLevels', function ($q) use ($user) {
                    $q->where('warehouse_id', $user->warehouse_id);
                });
            }

            $perPage = $request->get('per_page', 15);
            $productsPaginated = $query->orderBy('name')->paginate($perPage);

            // Get the products and transform them
            $products = $productsPaginated->items();
            $transformedProducts = collect($products)->map(function ($product) use ($user) {
                $stockLevels = $product->stockLevels;

                // Filter stock levels by user's warehouse if applicable
                if ($user->warehouse_id) {
                    $stockLevels = $stockLevels->where('warehouse_id', $user->warehouse_id);
                }

                $totalStock = $stockLevels->sum('available_qty');
                $totalReserved = $stockLevels->sum('reserved_qty');

                return [
                    'id' => $product->id,
                    'name' => $product->name,
                    'sku' => $product->sku,
                    'category' => $product->category,
                    'unit' => $product->unit,
                    'cost_price' => $product->cost_price,
                    'sell_price' => $product->sell_price,
                    'reorder_level' => $product->reorder_level,
                    'description' => $product->description,
                    'barcode' => $product->barcode,
                    'status' => $product->status,
                    'total_stock' => $totalStock,
                    'reserved_stock' => $totalReserved,
                    'available_stock' => $totalStock - $totalReserved,
                    'is_low_stock' => $totalStock <= $product->reorder_level,
                    'stock_levels' => $stockLevels->map(function ($stock) {
                        return [
                            'warehouse' => $stock->warehouse,
                            'available_qty' => $stock->available_qty,
                            'reserved_qty' => $stock->reserved_qty,
                        ];
                    }),
                    'created_at' => $product->created_at,
                    'updated_at' => $product->updated_at,
                ];
            });

            // Create response with pagination meta
            $response = [
                'data' => $transformedProducts,
                'current_page' => $productsPaginated->currentPage(),
                'last_page' => $productsPaginated->lastPage(),
                'per_page' => $productsPaginated->perPage(),
                'total' => $productsPaginated->total(),
                'from' => $productsPaginated->firstItem(),
                'to' => $productsPaginated->lastItem(),
            ];

            return response()->json([
                'success' => true,
                'data' => $response
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get products',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Store a newly created product
     */
    public function store(Request $request)
    {
        try {
            $user = $request->user();

            if (!$user->can('product.create')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized access'
                ], 403);
            }

            $request->validate([
                'name' => 'required|string|max:255',
                'sku' => 'required|string|unique:products,sku|max:255',
                'category_id' => 'required|exists:categories,id',
                'unit' => 'required|string|max:50',
                'cost_price' => 'required|numeric|min:0',
                'sell_price' => 'required|numeric|min:0',
                'reorder_level' => 'required|integer|min:0',
                'description' => 'nullable|string',
                'barcode' => 'nullable|string|unique:products,barcode',
                'status' => 'in:active,inactive',
            ]);

            $product = Product::create($request->all());
            $product->load('category');

            return response()->json([
                'success' => true,
                'message' => 'Product created successfully',
                'data' => $product
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
                'message' => 'Failed to create product',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified product
     */
    public function show(Request $request, Product $product)
    {
        try {
            $user = $request->user();

            if (!$user->can('product.view')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized access'
                ], 403);
            }

            $product->load(['category', 'stockLevels.warehouse']);

            // Filter stock levels by user's warehouse if applicable
            if ($user->warehouse_id) {
                $product->stockLevels = $product->stockLevels->where('warehouse_id', $user->warehouse_id);
            }

            $totalStock = $product->stockLevels->sum('available_qty');
            $totalReserved = $product->stockLevels->sum('reserved_qty');

            $productData = [
                'id' => $product->id,
                'name' => $product->name,
                'sku' => $product->sku,
                'category' => $product->category,
                'unit' => $product->unit,
                'cost_price' => $product->cost_price,
                'sell_price' => $product->sell_price,
                'reorder_level' => $product->reorder_level,
                'description' => $product->description,
                'barcode' => $product->barcode,
                'status' => $product->status,
                'metadata' => $product->metadata,
                'total_stock' => $totalStock,
                'reserved_stock' => $totalReserved,
                'available_stock' => $totalStock - $totalReserved,
                'is_low_stock' => $totalStock <= $product->reorder_level,
                'stock_levels' => $product->stockLevels->map(function ($stock) {
                    return [
                        'id' => $stock->id,
                        'warehouse' => $stock->warehouse,
                        'available_qty' => $stock->available_qty,
                        'reserved_qty' => $stock->reserved_qty,
                        'updated_at' => $stock->updated_at,
                    ];
                }),
                'created_at' => $product->created_at,
                'updated_at' => $product->updated_at,
            ];

            return response()->json([
                'success' => true,
                'data' => $productData
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get product',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update the specified product
     */
    public function update(Request $request, Product $product)
    {
        try {
            $user = $request->user();

            if (!$user->can('product.edit')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized access'
                ], 403);
            }

            $request->validate([
                'name' => 'sometimes|required|string|max:255',
                'sku' => 'sometimes|required|string|max:255|unique:products,sku,' . $product->id,
                'category_id' => 'sometimes|required|exists:categories,id',
                'unit' => 'sometimes|required|string|max:50',
                'cost_price' => 'sometimes|required|numeric|min:0',
                'sell_price' => 'sometimes|required|numeric|min:0',
                'reorder_level' => 'sometimes|required|integer|min:0',
                'description' => 'nullable|string',
                'barcode' => 'nullable|string|unique:products,barcode,' . $product->id,
                'status' => 'sometimes|in:active,inactive',
            ]);

            $product->update($request->all());
            $product->load('category');

            return response()->json([
                'success' => true,
                'message' => 'Product updated successfully',
                'data' => $product
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
                'message' => 'Failed to update product',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified product
     */
    public function destroy(Request $request, Product $product)
    {
        try {
            $user = $request->user();

            if (!$user->can('product.delete')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized access'
                ], 403);
            }

            // Check if product has stock
            $hasStock = $product->stockLevels()->where('available_qty', '>', 0)->exists();

            if ($hasStock) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot delete product with existing stock'
                ], 422);
            }

            $product->delete();

            return response()->json([
                'success' => true,
                'message' => 'Product deleted successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete product',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
