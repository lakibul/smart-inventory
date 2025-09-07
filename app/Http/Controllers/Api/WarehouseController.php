<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Warehouse;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class WarehouseController extends Controller
{
    /**
     * Display a listing of warehouses
     */
    public function index(Request $request)
    {
        try {
            $user = $request->user();

            if (!$user->can('warehouse.view')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized access'
                ], 403);
            }

            $query = Warehouse::with(['manager', 'users'])
                ->withCount(['users', 'stockLevels']);

            // Filter by search term
            if ($request->has('search')) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('code', 'like', "%{$search}%")
                      ->orWhere('location', 'like', "%{$search}%");
                });
            }

            // Filter by status
            if ($request->has('status')) {
                $query->where('status', $request->status);
            } else {
                $query->where('status', 'active');
            }

            $warehouses = $query->orderBy('name')->get();

            return response()->json([
                'success' => true,
                'data' => $warehouses
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get warehouses',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Store a newly created warehouse
     */
    public function store(Request $request)
    {
        try {
            $user = $request->user();

            if (!$user->can('warehouse.create')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized access'
                ], 403);
            }

            $request->validate([
                'name' => 'required|string|max:255',
                'code' => 'required|string|unique:warehouses,code|max:50',
                'location' => 'nullable|string',
                'manager_id' => 'nullable|exists:users,id',
                'status' => 'in:active,inactive',
            ]);

            $warehouse = Warehouse::create($request->all());
            $warehouse->load('manager');

            return response()->json([
                'success' => true,
                'message' => 'Warehouse created successfully',
                'data' => $warehouse
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
                'message' => 'Failed to create warehouse',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified warehouse
     */
    public function show(Request $request, Warehouse $warehouse)
    {
        try {
            $user = $request->user();

            if (!$user->can('warehouse.view')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized access'
                ], 403);
            }

            $warehouse->load([
                'manager',
                'users',
                'stockLevels.product',
                'stockLevels' => function ($query) {
                    $query->where('available_qty', '>', 0);
                }
            ]);

            // Calculate statistics
            $totalProducts = $warehouse->stockLevels->count();
            $totalStock = $warehouse->stockLevels->sum('available_qty');
            $totalValue = $warehouse->stockLevels->sum(function ($stock) {
                return $stock->available_qty * $stock->product->cost_price;
            });

            $warehouseData = $warehouse->toArray();
            $warehouseData['statistics'] = [
                'total_products' => $totalProducts,
                'total_stock' => $totalStock,
                'total_value' => round($totalValue, 2),
            ];

            return response()->json([
                'success' => true,
                'data' => $warehouseData
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get warehouse',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update the specified warehouse
     */
    public function update(Request $request, Warehouse $warehouse)
    {
        try {
            $user = $request->user();

            if (!$user->can('warehouse.edit')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized access'
                ], 403);
            }

            $request->validate([
                'name' => 'sometimes|required|string|max:255',
                'code' => 'sometimes|required|string|max:50|unique:warehouses,code,' . $warehouse->id,
                'location' => 'nullable|string',
                'manager_id' => 'nullable|exists:users,id',
                'status' => 'sometimes|in:active,inactive',
            ]);

            $warehouse->update($request->all());
            $warehouse->load('manager');

            return response()->json([
                'success' => true,
                'message' => 'Warehouse updated successfully',
                'data' => $warehouse
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
                'message' => 'Failed to update warehouse',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified warehouse
     */
    public function destroy(Request $request, Warehouse $warehouse)
    {
        try {
            $user = $request->user();

            if (!$user->can('warehouse.delete')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized access'
                ], 403);
            }

            // Check if warehouse has stock
            if ($warehouse->stockLevels()->where('available_qty', '>', 0)->exists()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot delete warehouse with existing stock'
                ], 422);
            }

            // Check if warehouse has assigned users
            if ($warehouse->users()->exists()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot delete warehouse with assigned users'
                ], 422);
            }

            $warehouse->delete();

            return response()->json([
                'success' => true,
                'message' => 'Warehouse deleted successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete warehouse',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
