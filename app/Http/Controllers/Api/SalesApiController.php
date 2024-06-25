<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use DateInterval;
use DateTime;
use Illuminate\Http\Request;

class SalesApiController extends Controller
{
    public function report(Request $request)
    {
        $start_date = $request->input('start_date');
        $end_date = $request->input('end_date') . ' 23:59:59';
        $orders = Order::whereBetween('created_at', [$start_date, $end_date])->orderBy('created_at')->get();
        $orders_report = [];

        // Set to month of first day
        $orders_report_start = new DateTime($start_date);
        $orders_report_start->modify($orders_report_start->format('Y-m-1'));
        $orders_report_end = new DateTime($end_date);
        $orders_report_end->modify($orders_report_end->format('Y-m-1'));

        if (
            $orders_report_start->format('Y') === $orders_report_end->format('Y')
        ) {
            $format = 'F';
        } else {
            $format = 'Y F';
        }

        while ($orders_report_start->format('Y-m') !== $orders_report_end->format('Y-m')) {
            $orders_report[$orders_report_start->format($format)] = 0;
            $orders_report_start->add(new DateInterval("P1M"));
        }

        foreach ($orders as $order) {
            $datetime = new DateTime($order->datetime);
            $key = $datetime->format($format);
            if (!array_key_exists($key, $orders_report)) {
                $orders_report[$key] = 0;
            }
            $orders_report[$key] += $order->total;
        }
        return ['data' => array_values($orders_report), 'labels' => array_keys($orders_report)];
    }
}
