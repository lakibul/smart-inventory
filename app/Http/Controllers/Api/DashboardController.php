<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\StockLevel;
use App\Models\Warehouse;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    /**
     * Get dashboard statistics
     */
    public function stats(Request $request)
    {
        try {
            $user = $request->user();

            // Check permissions
            if (!$user->can('dashboard.view')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized access'
                ], 403);
            }

            // Filter by warehouse if user is assigned to specific warehouse
            $warehouseId = $user->warehouse_id;

            // Total products
            $totalProducts = Product::where('status', 'active')->count();

            // Total warehouses (only for users with warehouse.view permission)
            $totalWarehouses = $user->can('warehouse.view')
                ? Warehouse::where('status', 'active')->count()
                : 0;

            // Total stock value
            $stockQuery = StockLevel::with('product');
            if ($warehouseId) {
                $stockQuery->where('warehouse_id', $warehouseId);
            }

            $stockLevels = $stockQuery->get();
            $totalStockValue = $stockLevels->sum(function ($stock) {
                return $stock->available_qty * $stock->product->cost_price;
            });

            // Low stock products
            $lowStockProducts = Product::with(['stockLevels' => function ($query) use ($warehouseId) {
                if ($warehouseId) {
                    $query->where('warehouse_id', $warehouseId);
                }
            }])
            ->where('status', 'active')
            ->get()
            ->filter(function ($product) {
                $totalStock = $product->stockLevels->sum('available_qty');
                return $totalStock <= $product->reorder_level && $totalStock > 0;
            })
            ->take(5)
            ->map(function ($product) {
                return [
                    'id' => $product->id,
                    'name' => $product->name,
                    'sku' => $product->sku,
                    'current_stock' => $product->stockLevels->sum('available_qty'),
                    'reorder_level' => $product->reorder_level,
                ];
            });

            // Out of stock products
            $outOfStockProducts = Product::with(['stockLevels' => function ($query) use ($warehouseId) {
                if ($warehouseId) {
                    $query->where('warehouse_id', $warehouseId);
                }
            }])
            ->where('status', 'active')
            ->get()
            ->filter(function ($product) {
                return $product->stockLevels->sum('available_qty') == 0;
            })
            ->count();

            // Recent activity (products added in last 7 days)
            $recentProducts = Product::where('created_at', '>=', now()->subDays(7))
                ->where('status', 'active')
                ->count();

            return response()->json([
                'success' => true,
                'data' => [
                    'total_products' => $totalProducts,
                    'total_warehouses' => $totalWarehouses,
                    'total_stock_value' => round($totalStockValue, 2),
                    'low_stock_count' => $lowStockProducts->count(),
                    'out_of_stock_count' => $outOfStockProducts,
                    'recent_products' => $recentProducts,
                    'low_stock_products' => $lowStockProducts,
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get dashboard stats',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get stock level chart data
     */
    public function stockChart(Request $request)
    {
        try {
            $user = $request->user();

            if (!$user->can('dashboard.view')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized access'
                ], 403);
            }

            $warehouseId = $user->warehouse_id;

            // Get stock levels by category
            $query = Product::with(['category', 'stockLevels' => function ($q) use ($warehouseId) {
                if ($warehouseId) {
                    $q->where('warehouse_id', $warehouseId);
                }
            }])
            ->where('status', 'active');

            $products = $query->get();

            $categoryData = $products->groupBy('category.name')->map(function ($products, $category) {
                $totalStock = $products->sum(function ($product) {
                    return $product->stockLevels->sum('available_qty');
                });

                return [
                    'category' => $category ?: 'Uncategorized',
                    'stock_count' => $totalStock,
                ];
            })->values();

            return response()->json([
                'success' => true,
                'data' => $categoryData
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get chart data',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
