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
    // Doanh thu theo khoảng thời gian
    public function getRevenueByPeriod(Request $request)
    {
        $groupBy = $request->input('group_by', 'month'); // default
        $year = $request->input('year');
        $month = $request->input('month');
        $fromDate = $request->input('from_date'); // yyyy-mm-dd
        $toDate = $request->input('to_date');     // yyyy-mm-dd

        $query = DB::table('orders')
            ->whereNotIn('order_status_id', [7, 8]); // loại trừ đơn hủy/hoàn

        if ($year) {
            $query->whereYear('created_at', $year);
        }

        if ($month) {
            $query->whereMonth('created_at', $month);
        }

        if ($fromDate && $toDate) {
            $query->whereBetween('created_at', [$fromDate, $toDate]);
        } elseif ($fromDate) {
            $query->whereDate('created_at', '>=', $fromDate);
        } elseif ($toDate) {
            $query->whereDate('created_at', '<=', $toDate);
        }

        // Parse dates for reusability
        $from = Carbon::parse($fromDate);
        $to = Carbon::parse($toDate);

        switch ($groupBy) {
            case 'weekday':
                $rawResults = $query->select(
                    DB::raw('WEEKDAY(created_at) as weekday'),
                    DB::raw('SUM(total_price) as revenue')
                )
                    ->groupBy('weekday')
                    ->orderBy('weekday')
                    ->get()
                    ->mapWithKeys(function ($item) {
                        return [
                            $item->weekday => (float) $item->revenue,
                        ];
                    });

                $weekdayMap = [
                    0 => 'Thứ Hai',
                    1 => 'Thứ Ba',
                    2 => 'Thứ Tư',
                    3 => 'Thứ Năm',
                    4 => 'Thứ Sáu',
                    5 => 'Thứ Bảy',
                    6 => 'Chủ Nhật',
                ];

                $results = [];
                foreach ($weekdayMap as $i => $label) {
                    $results[] = [
                        'weekday' => $label,
                        'revenue' => $rawResults[$i] ?? 0,
                    ];
                }
                break;

            case 'day':
                $rawResults = $query->select(
                    DB::raw('DATE(created_at) as date'),
                    DB::raw('SUM(total_price) as revenue')
                )
                    ->groupBy('date')
                    ->orderBy('date')
                    ->get()
                    ->mapWithKeys(function ($item) {
                        return [
                            $item->date => (float) $item->revenue,
                        ];
                    });

                $results = [];
                for ($date = $from->copy(); $date->lte($to); $date->addDay()) {
                    $formattedDate = $date->toDateString();
                    $results[] = [
                        'date' => $formattedDate,
                        'revenue' => $rawResults[$formattedDate] ?? 0,
                    ];
                }
                break;

            case 'week':
                $rawResults = $query->select(
                    DB::raw('YEAR(created_at) as year'),
                    DB::raw('WEEK(created_at, 1) as week'),
                    DB::raw('SUM(total_price) as revenue')
                )
                    ->groupBy('year', 'week')
                    ->get()
                    ->mapWithKeys(function ($item) {
                        $key = $item->year . '-' . str_pad($item->week, 2, '0', STR_PAD_LEFT);
                        return [
                            $key => (float) $item->revenue,
                        ];
                    });

                $results = [];
                while ($from->lte($to)) {
                    $year = $from->year;
                    $week = $from->isoWeek();
                    $start = $from->copy();
                    $end = $from->copy()->endOfWeek();
                    $key = $year . '-' . str_pad($week, 2, '0', STR_PAD_LEFT);
                    $label = sprintf('Tuần %d (%s - %s)', $week, $start->format('d/m'), $end->format('d/m'));

                    $results[] = [
                        'week' => $label,
                        'revenue' => $rawResults[$key] ?? 0,
                    ];

                    $from->addWeek();
                }
                break;

            case 'month':
                $rawResults = $query->select(
                    DB::raw('YEAR(created_at) as year'),
                    DB::raw('MONTH(created_at) as month'),
                    DB::raw('SUM(total_price) as revenue')
                )
                    ->groupBy('year', 'month')
                    ->get()
                    ->mapWithKeys(function ($item) {
                        $key = $item->year . '-' . str_pad($item->month, 2, '0', STR_PAD_LEFT);
                        return [
                            $key => (float) $item->revenue,
                        ];
                    });

                $results = [];
                while ($from->lte($to)) {
                    $year = $from->year;
                    $month = $from->month;
                    $key = $year . '-' . str_pad($month, 2, '0', STR_PAD_LEFT);
                    $results[] = [
                        'month' => sprintf('%02d/%d', $month, $year),
                        'revenue' => $rawResults[$key] ?? 0,
                    ];
                    $from->addMonth();
                }
                break;

            case 'quarter':
                $rawResults = $query->select(
                    DB::raw('YEAR(created_at) as year'),
                    DB::raw('QUARTER(created_at) as quarter'),
                    DB::raw('SUM(total_price) as revenue')
                )
                    ->groupBy('year', 'quarter')
                    ->get()
                    ->mapWithKeys(function ($item) {
                        $key = $item->year . '-Q' . $item->quarter;
                        return [
                            $key => (float) $item->revenue,
                        ];
                    });

                $results = [];
                while ($from->lte($to)) {
                    $year = $from->year;
                    $quarter = ceil($from->month / 3);
                    $key = $year . '-Q' . $quarter;

                    $results[] = [
                        'quarter' => 'Q' . $quarter . ' ' . $year,
                        'revenue' => $rawResults[$key] ?? 0,
                    ];

                    $from->addQuarter();
                }
                break;

            case 'year':
                $rawResults = $query->select(
                    DB::raw('YEAR(created_at) as year'),
                    DB::raw('SUM(total_price) as revenue')
                )
                    ->groupBy('year')
                    ->get()
                    ->mapWithKeys(function ($item) {
                        return [$item->year => (float) $item->revenue];
                    });

                $startYear = $from->year;
                $endYear = $to->year;
                $results = [];

                for ($y = $startYear; $y <= $endYear; $y++) {
                    $results[] = [
                        'year' => $y,
                        'revenue' => $rawResults[$y] ?? 0,
                    ];
                }
                break;

            default:
                return response()->json([
                    'error' => 'Tham số group_by không hợp lệ. Giá trị hợp lệ: weekday, day, week, month, quarter, year'
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
