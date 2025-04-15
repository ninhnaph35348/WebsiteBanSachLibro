<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StatisticsController extends Controller
{
    // Tổng số lượng sách
    public function getTotalBooks()
    {
        $totalBooks = DB::table('products')
            ->where('del_flg', 0)
            ->count();

        return response()->json([
            'Tổng số lượng sách' => $totalBooks
        ]);
    }
    // Số lượng sách đã bán
    public function getSoldBooks()
    {
        $soldBooks = DB::table('order_details')
            ->join('product_variants', 'order_details.product_variant_id', '=', 'product_variants.id')
            ->join('products', 'product_variants.product_id', '=', 'products.id')
            ->where('products.del_flg', 0)
            ->sum('order_details.quantity');

        return response()->json([
            'Số lượng sách đã bán' => $soldBooks
        ]);
    }
    // Số lượng sách còn trong kho
    public function getInStock()
    {
        $inStock = DB::table('product_variants')
            ->join('products', 'product_variants.product_id', '=', 'products.id')
            ->where('products.del_flg', 0)
            ->where('product_variants.del_flg', 0)
            ->sum('product_variants.quantity');

        return response()->json([
            'Số lượng sách còn trong kho' => $inStock
        ]);
    }
    // Doanh thu từ sách bán ra
    public function getTotalRevenue()
    {
        $totalRevenue = DB::table('orders')
            ->whereNotIn('order_status_id', [7, 8]) // Loại trừ đơn hàng đã hủy và hoàn trả
            ->sum('total_price');

        return response()->json([
            'Doanh thu từ sách bán ra' => $totalRevenue
        ]);
    }
    // Doanh thu theo từng ngày/tháng/năm
    public function getRevenueByPeriod(Request $request)
    {
        $groupBy = $request->input('group_by', 'month'); // default
        $year = $request->input('year');
        $month = $request->input('month');

        $query = DB::table('orders')
            ->whereNotIn('order_status_id', [7, 8]); // loại trừ đơn hủy/hoàn

        if ($year) {
            $query->whereYear('created_at', $year);
        }

        if ($month) {
            $query->whereMonth('created_at', $month);
        }

        switch ($groupBy) {
            case 'day':
                $results = $query->select(
                    DB::raw('DATE(created_at) as date'),
                    DB::raw('SUM(total_price) as revenue')
                )
                    ->groupBy('date')
                    ->orderBy('date')
                    ->get()
                    ->map(function ($item) {
                        return [
                            'date' => $item->date,
                            'revenue' => (float) $item->revenue,
                        ];
                    });
                break;

            case 'week':
                $results = $query->select(
                    DB::raw('YEAR(created_at) as year'),
                    DB::raw('WEEK(created_at, 1) as week'),
                    DB::raw('MIN(DATE(created_at)) as week_start'),
                    DB::raw('MAX(DATE(created_at)) as week_end'),
                    DB::raw('SUM(total_price) as revenue')
                )
                    ->groupBy('year', 'week')
                    ->orderBy('year')
                    ->orderBy('week')
                    ->get()
                    ->map(function ($item) {
                        $label = sprintf(
                            'Tuần %d (%s - %s)',
                            $item->week,
                            Carbon::parse($item->week_start)->format('d/m'),
                            Carbon::parse($item->week_end)->format('d/m')
                        );
                        return [
                            'week' => $label,
                            'revenue' => (float) $item->revenue,
                        ];
                    });
                break;

            case 'month':
                $results = $query->select(
                    DB::raw('YEAR(created_at) as year'),
                    DB::raw('MONTH(created_at) as month'),
                    DB::raw('SUM(total_price) as revenue')
                )
                    ->groupBy('year', 'month')
                    ->orderBy('year')
                    ->orderBy('month')
                    ->get()
                    ->map(function ($item) {
                        return [
                            'month' => sprintf('%02d/%d', $item->month, $item->year),
                            'revenue' => (float) $item->revenue,
                        ];
                    });
                break;
            case 'quarter':
                $results = $query->select(
                    DB::raw('YEAR(created_at) as year'),
                    DB::raw('QUARTER(created_at) as quarter'),
                    DB::raw('SUM(total_price) as revenue')
                )
                    ->groupBy('year', 'quarter')
                    ->orderBy('year')
                    ->orderBy('quarter')
                    ->get()
                    ->map(function ($item) {
                        return [
                            'quarter' => 'Q' . $item->quarter . ' ' . $item->year,
                            'revenue' => (float) $item->revenue,
                        ];
                    });
                break;
            case 'year':
                $results = $query->select(
                    DB::raw('YEAR(created_at) as year'),
                    DB::raw('SUM(total_price) as revenue')
                )
                    ->groupBy('year')
                    ->orderBy('year')
                    ->get()
                    ->map(function ($item) {
                        return [
                            'year' => $item->year,
                            'revenue' => (float) $item->revenue,
                        ];
                    });
                break;

            default:
                return response()->json([
                    'error' => 'Tham số group_by không hợp lệ. Giá trị hợp lệ: day, week, month, year'
                ], 400);
        }

        return response()->json($results);
    }
    // Số lượng khách hàng đã đặt hàng
    public function getCustomerCount()
    {
        $customerCount = DB::table('orders')
            ->whereNotNull('user_id')
            ->distinct('user_id')
            ->count('user_id');

        return response()->json([
            'customer_count' => $customerCount
        ]);
    }
    // Tống số lượng bình luận
    public function getTotalReviews()
    {
        $totalReviews = DB::table('reviews')
            ->where('del_flg', 0)
            ->where('status', 0) // Chỉ tính các bình luận active
            ->count();

        return response()->json([
            'total_reviews' => $totalReviews
        ]);
    }
    // Thống kê đơn hàng theo trạng thái
    public function getOrdersByStatus()
    {
        $ordersByStatus = DB::table('orders')
            ->join('order_statuses', 'orders.order_status_id', '=', 'order_statuses.id')
            ->select(
                'order_statuses.id',
                'order_statuses.name',
                DB::raw('COUNT(orders.id) as order_count'),
            )
            ->groupBy('order_statuses.id', 'order_statuses.name')
            ->get();

        return response()->json($ordersByStatus);
    }
}
