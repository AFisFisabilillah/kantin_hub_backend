<?php

namespace App\Http\Controllers;

use App\Models\Admin;
use App\Models\Product;
use App\Models\Service;
use DB;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        // 1. Tentukan Range Tanggal
        $startDate = null;
        $endDate = Carbon::now();

        if ($request->range == 'today') {
            $startDate = Carbon::today();
            $endDate = Carbon::now()->endOfDay();
        } elseif ($request->range == 'this_month') {
            $startDate = Carbon::now()->startOfMonth();
            $endDate = Carbon::now()->endOfMonth();
        } elseif ($request->range == 'this_year') {
            $startDate = Carbon::now()->startOfYear();
            $endDate = Carbon::now()->endOfYear();
        } elseif ($request->start_date && $request->end_date) {
            $startDate = Carbon::parse($request->start_date)->startOfDay();
            $endDate = Carbon::parse($request->end_date)->endOfDay();
        }

        // 2. Overview Data (Difilter berdasarkan tanggal kecuali total admin & total product)
        $totalProducts = Product::count();
        $totalAdmins = Admin::count();

        $serviceQuery = Service::query();
        if ($startDate) {
            $serviceQuery->whereBetween('created_at', [$startDate, $endDate]);
        }

        $totalServices = (clone $serviceQuery)->count();
        $totalRevenue = (clone $serviceQuery)->sum('total_cost');

        // 3. Recent Activities
        $recentActivities = (clone $serviceQuery)
            ->latest('updated_at')
            ->limit(5)
            ->get()
            ->map(function ($service) {
                return [
                    'id' => $service->id,
                    'type' => 'service',
                    'title' => $this->formatTitle($service->status),
                    'description' => "{$service->laptop_brand} {$service->laptop_model} - {$service->customer_name}",
                    'status' => $service->status,
                    'time' => $service->updated_at->diffForHumans(),
                ];
            });

        // 4. Service Stats (Berdasarkan Range)
        $serviceStatsByStatus = (clone $serviceQuery)
            ->select('status', DB::raw('count(*) as total'))
            ->groupBy('status')
            ->pluck('total', 'status')
            ->toArray();

        // 5. Product Stats
        $lowStock = Product::where('stok', '>', 0)->where('stok', '<=', 10)->count();
        $outOfStock = Product::where('stok', '<=', 0)->count();

        // Top Selling (Difilter berdasarkan tanggal di table pivot)
        $topSellingQuery = DB::table('service_products')
            ->join('products', 'service_products.product_id', '=', 'products.id')
            ->select('products.name', DB::raw('sum(service_products.qty) as sold'));

        if ($startDate) {
            $topSellingQuery->whereBetween('service_products.created_at', [$startDate, $endDate]);
        }

        $topSelling = $topSellingQuery->groupBy('products.id', 'products.name')
            ->orderByDesc('sold')
            ->limit(5)
            ->get();

        // 6. Revenue Data (Grafik Bulanan - Tetap 6 bulan terakhir agar grafik cantik)
        $monthlyRevenue = [];
        for ($i = 5; $i >= 0; $i--) {
            $month = Carbon::now()->subMonths($i);
            $revenue = Service::whereYear('created_at', $month->year)
                ->whereMonth('created_at', $month->month)
                ->sum('total_cost');

            $monthlyRevenue[] = [
                'month' => $month->format('M'),
                'revenue' => (int) $revenue
            ];
        }

        // 7. Category Ratio (Difilter berdasarkan tanggal)
        $totalServiceCost = (clone $serviceQuery)->sum('service_cost');

        $productInServiceQuery = DB::table('service_products');
        if ($startDate) {
            $productInServiceQuery->whereBetween('created_at', [$startDate, $endDate]);
        }
        $totalProductInService = $productInServiceQuery->sum('subtotal');

        $grandTotal = $totalServiceCost + $totalProductInService;

        $categories = [
            [
                'category' => 'Service',
                'value' => $grandTotal > 0 ? round(($totalServiceCost / $grandTotal) * 100) : 0
            ],
            [
                'category' => 'Products',
                'value' => $grandTotal > 0 ? round(($totalProductInService / $grandTotal) * 100) : 0
            ]
        ];

        return response()->json([
            'dashboard' => [
                'overview' => [
                    'totalProducts' => $totalProducts,
                    'totalServices' => $totalServices,
                    'totalAdmins' => $totalAdmins,
                    'totalRevenue' => (int) $totalRevenue,
                ],
                'recentActivities' => $recentActivities,
                'serviceStats' => [
                    'total' => $totalServices,
                    'byStatus' => [
                        'received' => $serviceStatsByStatus['received'] ?? 0,
                        'process' => $serviceStatsByStatus['process'] ?? 0,
                        'done' => $serviceStatsByStatus['done'] ?? 0,
                        'taken' => $serviceStatsByStatus['taken'] ?? 0,
                        'cancelled' => $serviceStatsByStatus['cancelled'] ?? 0,
                    ]
                ],
                'productStats' => [
                    'total' => $totalProducts,
                    'lowStock' => $lowStock,
                    'outOfStock' => $outOfStock,
                    'topSelling' => $topSelling
                ],
                'revenueData' => [
                    'monthly' => $monthlyRevenue,
                    'categories' => $categories
                ]
            ]
        ]);
    }

    private function formatTitle($status)
    {
        return match ($status) {
            'received' => 'New Service Request',
            'done' => 'Service Completed',
            'process' => 'Service in Progress',
            'taken' => 'Device Collected',
            'cancelled' => 'Service Cancelled',
            default => 'Service Update'
        };
    }
}
