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
    public function statistics(Request $request)
    {
        $period = $request->get('period', 'today'); // today, week, month, year

        $today = Carbon::today();
        $startDate = null;
        $endDate = $today;

        switch ($period) {
            case 'today':
                $startDate = $today;
                break;
            case 'week':
                $startDate = $today->copy()->startOfWeek();
                break;
            case 'month':
                $startDate = $today->copy()->startOfMonth();
                break;
            case 'year':
                $startDate = $today->copy()->startOfYear();
                break;
        }

        // Total Sales
        $totalSalesQuery = Sale::query();
        if ($startDate) {
            $totalSalesQuery->whereDate('created_at', '>=', $startDate)
                ->whereDate('created_at', '<=', $endDate);
        }
        $totalSales = $totalSalesQuery->count();
        $revenue = $totalSalesQuery->sum('total_price');

        // Revenue Growth
        $previousPeriodRevenue = $this->getPreviousPeriodRevenue($period);
        $revenueGrowth = $previousPeriodRevenue > 0
            ? (($revenue - $previousPeriodRevenue) / $previousPeriodRevenue) * 100
            : 0;

        // Total Products
        $totalProducts = Product::count();

        // Low Stock Products
        $lowStockProducts = Product::where('stok', '<', 10)->count();

        // Recent Sales
        $recentSales = Sale::with(['items.product'])
            ->latest()
            ->take(5)
            ->get();

        // Sales Chart Data
        $chartData = $this->getSalesChartData($period);

        return response()->json([
            'success' => true,
            'data' => [
                'total_sales' => $totalSales,
                'revenue' => $revenue,
                'revenue_growth' => round($revenueGrowth, 2),
                'total_products' => $totalProducts,
                'low_stock_products' => $lowStockProducts,
                'recent_sales' => $recentSales,
                'chart_data' => $chartData,
            ]
        ]);
    }

    private function getPreviousPeriodRevenue($period)
    {
        $today = Carbon::today();

        switch ($period) {
            case 'today':
                return Sale::whereDate('created_at', $today->copy()->subDay())->sum('total_price');
            case 'week':
                return Sale::whereBetween('created_at', [
                    $today->copy()->subWeek()->startOfWeek(),
                    $today->copy()->subWeek()->endOfWeek()
                ])->sum('total_price');
            case 'month':
                return Sale::whereMonth('created_at', $today->copy()->subMonth()->month)
                    ->whereYear('created_at', $today->copy()->subMonth()->year)
                    ->sum('total_price');
            case 'year':
                return Sale::whereYear('created_at', $today->copy()->subYear()->year)
                    ->sum('total_price');
            default:
                return 0;
        }
    }

    private function getSalesChartData($period)
    {
        $data = [];
        $today = Carbon::today();

        if ($period === 'today') {
            // Hourly data for today
            for ($i = 0; $i < 24; $i++) {
                $hour = $today->copy()->hour($i);
                $nextHour = $hour->copy()->addHour();

                $sales = Sale::whereBetween('created_at', [$hour, $nextHour])
                    ->sum('total_price');

                $data[] = [
                    'name' => $hour->format('H:00'),
                    'sales' => $sales ?? 0,
                ];
            }
        } elseif ($period === 'week') {
            // Daily data for week
            for ($i = 6; $i >= 0; $i--) {
                $date = $today->copy()->subDays($i);
                $sales = Sale::whereDate('created_at', $date)
                    ->sum('total_price');

                $data[] = [
                    'name' => $date->format('D'),
                    'sales' => $sales ?? 0,
                    'full_date' => $date->format('Y-m-d'),
                ];
            }
        } elseif ($period === 'month') {
            // Weekly data for month
            $startOfMonth = $today->copy()->startOfMonth();
            $endOfMonth = $today->copy()->endOfMonth();

            $current = $startOfMonth->copy();
            $week = 1;

            while ($current->lt($endOfMonth)) {
                $weekEnd = $current->copy()->endOfWeek();
                if ($weekEnd->gt($endOfMonth)) {
                    $weekEnd = $endOfMonth;
                }

                $sales = Sale::whereBetween('created_at', [$current, $weekEnd])
                    ->sum('total_price');

                $data[] = [
                    'name' => 'Week ' . $week,
                    'sales' => $sales ?? 0,
                    'period' => $current->format('M d') . ' - ' . $weekEnd->format('M d'),
                ];

                $current = $weekEnd->copy()->addDay();
                $week++;
            }
        } else {
            // Monthly data for year
            for ($i = 1; $i <= 12; $i++) {
                $month = Carbon::create($today->year, $i, 1);
                $sales = Sale::whereYear('created_at', $today->year)
                    ->whereMonth('created_at', $i)
                    ->sum('total_price');

                $data[] = [
                    'name' => $month->format('M'),
                    'sales' => $sales ?? 0,
                    'month' => $month->format('F Y'),
                ];
            }
        }

        return $data;
    }
}
