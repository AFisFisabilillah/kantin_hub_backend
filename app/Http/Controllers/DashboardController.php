<?php

namespace App\Http\Controllers;

use App\Models\Admin;
use App\Models\Product;
use App\Models\Sale;
use App\Models\Service;
use DB;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        // 1. Setup Filter Tanggal
        $startDate = null;
        $endDate = Carbon::now()->endOfDay();

        if ($request->range == 'today') {
            $startDate = Carbon::today();
        } elseif ($request->range == 'this_month') {
            $startDate = Carbon::now()->startOfMonth();
        } elseif ($request->range == 'this_year') {
            $startDate = Carbon::now()->startOfYear();
        } elseif ($request->start_date && $request->end_date) {
            $startDate = Carbon::parse($request->start_date)->startOfDay();
            $endDate = Carbon::parse($request->end_date)->endOfDay();
        }

        // 2. Query Base dengan Filter
        $serviceQuery = Service::query();
        $saleQuery = Sale::query();
        if ($startDate) {
            $serviceQuery->whereBetween('created_at', [$startDate, $endDate]);
            $saleQuery->whereBetween('created_at', [$startDate, $endDate]);
        }

        // --- OVERVIEW ---
        $totalProducts = Product::count();
        $totalServices = (clone $serviceQuery)->count();
        $totalSales = (clone $saleQuery)->count();
        $totalAdmins = Admin::count();

        $revService = (clone $serviceQuery)->sum('total_cost');
        $revSale = (clone $saleQuery)->sum('total_price');

        $todayRevenue = Service::whereDate('created_at', Carbon::today())->sum('total_cost') +
            Sale::whereDate('created_at', Carbon::today())->sum('total_price');

        $monthlyRevenue = Service::whereMonth('created_at', Carbon::now()->month)->sum('total_cost') +
            Sale::whereMonth('created_at', Carbon::now()->month)->sum('total_price');

        // --- RECENT ACTIVITIES (Merged from multiple tables) ---
        $recentSales = Sale::latest()->limit(2)->get()->map(fn($item) => [
            'id' => $item->id, 'type' => 'sale', 'title' => 'New Sale', 'description' => "Invoice $item->invoice_number",
            'customer' => $item->customer_name, 'amount' => $item->total_price, 'status' => 'completed', 'time' => $item->created_at->diffForHumans()
        ]);

        $recentServices = Service::latest()->limit(2)->get()->map(fn($item) => [
            'id' => $item->id, 'type' => 'service', 'title' => 'New Service Request', 'description' => "Repair $item->laptop_brand",
            'status' => $item->status, 'time' => $item->created_at->diffForHumans()
        ]);

        $recentAdmins = Admin::latest()->limit(1)->get()->map(fn($item) => [
            'id' => $item->id, 'type' => 'admin', 'title' => 'Admin Created', 'username' => $item->username, 'status' => 'success', 'time' => $item->created_at->diffForHumans()
        ]);

        $activities = $recentSales->concat($recentServices)->concat($recentAdmins)->sortByDesc('time')->values()->take(5);

        // --- SERVICE STATS ---
        $serviceStatsByStatus = (clone $serviceQuery)->select('status', DB::raw('count(*) as total'))->groupBy('status')->pluck('total', 'status');
        $monthlyTrend = collect(range(1, 12))->map(fn($m) => Service::whereYear('created_at', date('Y'))->whereMonth('created_at', $m)->count());

        // --- PRODUCT STATS ---
        // Menghitung top selling gabungan dari Service dan Sale
        $topSelling = DB::table(function($query) {
            $query->select('product_id', 'qty')->from('service_products')
                ->unionAll(DB::table('sale_items')->select('product_id', 'qty'));
        }, 'combined_sales')
            ->join('products', 'combined_sales.product_id', '=', 'products.id')
            ->select('products.name', DB::raw('sum(combined_sales.qty) as sold'))
            ->groupBy('products.id', 'products.name')
            ->orderByDesc('sold')->limit(5)->get();

        // --- SALES STATS ---
        $salesByPayment = (clone $saleQuery)->select('payment_method', DB::raw('count(*) as total'))->groupBy('payment_method')->pluck('total', 'payment_method');

        // --- REVENUE DATA (Charts) ---
        $monthlyData = [];
        for ($i = 5; $i >= 0; $i--) {
            $month = Carbon::now()->subMonths($i);
            $monthlyData[] = [
                'month' => $month->format('M'),
                'revenue' => (int)(Service::whereMonth('created_at', $month->month)->sum('total_cost') + Sale::whereMonth('created_at', $month->month)->sum('total_price')),
                'sales' => Sale::whereMonth('created_at', $month->month)->count(),
                'services' => Service::whereMonth('created_at', $month->month)->count(),
            ];
        }

        $dailyRevenue = [];
        for ($i = 6; $i >= 0; $i--) {
            $day = Carbon::now()->subDays($i);
            $dailyRevenue[] = [
                'day' => $day->format('D'),
                'revenue' => (int)(Service::whereDate('created_at', $day)->sum('total_cost') + Sale::whereDate('created_at', $day)->sum('total_price'))
            ];
        }

        return response()->json([
            'dashboard' => [
                'overview' => [
                    'totalProducts' => $totalProducts,
                    'totalServices' => $totalServices,
                    'totalAdmins' => $totalAdmins,
                    'totalSales' => $totalSales,
                    'totalRevenue' => $revService + $revSale,
                    'todayRevenue' => (int)$todayRevenue,
                    'monthlyRevenue' => (int)$monthlyRevenue,
                ],
                'recentActivities' => $activities,
                'serviceStats' => [
                    'total' => $totalServices,
                    'byStatus' => [
                        'received' => $serviceStatsByStatus['received'] ?? 0,
                        'process' => $serviceStatsByStatus['process'] ?? 0,
                        'done' => $serviceStatsByStatus['done'] ?? 0,
                        'taken' => $serviceStatsByStatus['taken'] ?? 0,
                        'cancelled' => $serviceStatsByStatus['cancelled'] ?? 0,
                    ],
                    'monthlyTrend' => $monthlyTrend
                ],
                'productStats' => [
                    'total' => $totalProducts,
                    'lowStock' => Product::where('stok', '>', 0)->where('stok', '<=', 10)->count(),
                    'outOfStock' => Product::where('stok', '<=', 0)->count(),
                    'topSelling' => $topSelling
                ],
                'salesStats' => [
                    'total' => $totalSales,
                    'today' => Sale::whereDate('created_at', Carbon::today())->count(),
                    'monthly' => Sale::whereMonth('created_at', Carbon::now()->month)->count(),
                    'averageValue' => $totalSales > 0 ? round($revSale / $totalSales) : 0,
                    'byPaymentMethod' => $salesByPayment,
                    'recentSales' => Sale::latest()->limit(5)->get()->map(fn($s) => [
                        'invoice' => $s->invoice_number,
                        'customer' => $s->customer_name,
                        'amount' => $s->total_price,
                        'payment' => $s->payment_method,
                        'time' => $s->created_at->diffForHumans()
                    ])
                ],
                'revenueData' => [
                    'monthly' => $monthlyData,
                    'categories' => [
                        ['category' => 'Sales', 'value' => ($revService + $revSale) > 0 ? round(($revSale / ($revService + $revSale)) * 100) : 0],
                        ['category' => 'Services', 'value' => ($revService + $revSale) > 0 ? round(($revService / ($revService + $revSale)) * 100) : 0],
                    ],
                    'daily' => $dailyRevenue
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
