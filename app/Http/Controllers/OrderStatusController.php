<?php

namespace App\Http\Controllers;

use App\Models\OrderStatus;
use Illuminate\Http\Request;

class OrderStatusController extends Controller
{
    public function getAllOrderStatus()
    {
        $orderStatus = OrderStatus::all();
        return response()->json($orderStatus, 200);
    }
}
