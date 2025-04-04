<?php

namespace App\Http\Controllers;

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
        $groupBy = $request->input('group_by', 'month'); // mặc định group theo tháng
        $year = $request->input('year');
        $month = $request->input('month');
        
        $query = DB::table('orders')
            ->whereNotIn('order_status_id', [7, 8]); // Loại trừ đơn hủy/hoàn trả
        
        // Áp dụng filter theo năm nếu có
        if ($year) {
            $query->whereYear('created_at', $year);
        }
        
        // Áp dụng filter theo tháng nếu có
        if ($month) {
            $query->whereMonth('created_at', $month);
        }
        
        switch ($groupBy) {
            case 'day':
                $results = $query->select(
                    DB::raw('DATE(created_at) as date'),
                    DB::raw('YEAR(created_at) as year'),
                    DB::raw('MONTH(created_at) as month'),
                    DB::raw('DAY(created_at) as day'),
                    DB::raw('SUM(total_price) as revenue')
                )
                ->groupBy('date', 'year', 'month', 'day')
                ->orderBy('date', 'asc')
                ->get();
                break;
                
            case 'month':
                $results = $query->select(
                    DB::raw('YEAR(created_at) as year'),
                    DB::raw('MONTH(created_at) as month'),
                    DB::raw('SUM(total_price) as revenue')
                )
                ->groupBy('year', 'month')
                ->orderBy('year', 'asc')
                ->orderBy('month', 'asc')
                ->get();
                break;
                
            case 'year':
                $results = $query->select(
                    DB::raw('YEAR(created_at) as year'),
                    DB::raw('SUM(total_price) as revenue')
                )
                ->groupBy('year')
                ->orderBy('year', 'asc')
                ->get();
                break;
                
            default:
                return response()->json(['error' => 'Invalid group_by parameter. Accept: day, month, year'], 400);
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
