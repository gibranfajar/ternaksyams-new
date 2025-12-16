<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Payment;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{

    private function growthPercentage($current, $previous): float
    {
        if ($previous == 0) {
            return $current > 0 ? 100 : 0;
        }

        return round((($current - $previous) / $previous) * 100, 1);
    }


    public function index()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->only('email', 'password');

        if (Auth::attempt($credentials)) {
            return redirect()->intended('dashboard');
        }

        return back()->withErrors([
            'email' => 'The provided credentials do not match our records.',
        ]);
    }

    public function dashboardChart()
    {
        /* ================= WEEK (7 DAYS) ================= */
        $weekLabels = [];
        $weekVisitors = [];
        $weekUsers = [];
        $weekOrders = [];
        $weekSales = [];

        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i)->toDateString();
            $weekLabels[] = Carbon::parse($date)->format('D');

            // Unique Visitors
            // $weekVisitors[] = PageView::whereDate('created_at', $date)
            //     ->distinct('session_id')
            //     ->count();

            // Users
            $weekUsers[] = User::where('role', '!=', 'admin')->whereDate('created_at', $date)->count();

            // Orders
            $weekOrders[] = Order::whereHas(
                'payment',
                fn($q) =>
                $q->where('status', 'settlement')->whereDate('created_at', $date)
            )->count();

            // Sales
            $weekSales[] = Order::whereHas(
                'payment',
                fn($q) =>
                $q->where('status', 'settlement')->whereDate('created_at', $date)
            )->sum('total');
        }

        /* ================= MONTH (12 MONTHS) ================= */
        $monthLabels = [];
        $monthVisitors = [];
        $monthUsers = [];
        $monthOrders = [];
        $monthSales = [];

        for ($i = 11; $i >= 0; $i--) {
            $month = now()->subMonths($i);

            $monthLabels[] = $month->format('M');

            // $monthVisitors[] = PageView::whereYear('created_at', $month->year)
            //     ->whereMonth('created_at', $month->month)
            //     ->distinct('session_id')
            //     ->count();

            $monthUsers[] = User::where('role', '!=', 'admin')->whereYear('created_at', $month->year)
                ->whereMonth('created_at', $month->month)
                ->count();

            $monthOrders[] = Order::whereHas(
                'payment',
                fn($q) =>
                $q->where('status', 'settlement')
                    ->whereYear('created_at', $month->year)
                    ->whereMonth('created_at', $month->month)
            )->count();

            $monthSales[] = Order::whereHas(
                'payment',
                fn($q) =>
                $q->where('status', 'settlement')
                    ->whereYear('created_at', $month->year)
                    ->whereMonth('created_at', $month->month)
            )->sum('total');
        }

        return response()->json([
            'week' => [
                'labels' => $weekLabels,
                // 'visitors' => $weekVisitors,
                'users' => $weekUsers,
                'orders' => $weekOrders,
                'sales' => $weekSales,
            ],
            'month' => [
                'labels' => $monthLabels,
                // 'visitors' => $monthVisitors,
                'users' => $monthUsers,
                'orders' => $monthOrders,
                'sales' => $monthSales,
            ],
        ]);
    }

    public function incomeOverview()
    {
        $startWeek = now()->startOfWeek();
        $endWeek   = now()->endOfWeek();

        // SALES (paid)
        $sales = Order::whereHas(
            'payment',
            fn($q) =>
            $q->where('status', 'settlement')
                ->whereBetween('created_at', [$startWeek, $endWeek])
        )->sum('total');

        // ORDERS
        $orders = Order::whereHas(
            'payment',
            fn($q) =>
            $q->where('status', 'settlement')
                ->whereBetween('created_at', [$startWeek, $endWeek])
        )->count();

        // USERS
        $users = User::where('role', '!=', 'admin')->whereBetween('created_at', [$startWeek, $endWeek])->count();

        // UNIQUE VISITORS
        // $visitors = PageView::whereBetween('created_at', [$startWeek, $endWeek])
        //     ->distinct('session_id')
        //     ->count();

        return response()->json([
            'total' => $sales,
            'labels' => ['Sales', 'Orders', 'Users'],
            'series' => [
                (float) $sales,
                (float) $orders,
                (float) $users,
                // (float) $visitors
            ]
        ]);
    }


    public function dashboard()
    {
        $year = now()->year;
        $lastYear = now()->year - 1;

        /* ================= PAGE VIEWS ================= */
        // $pageViewsNow  = PageView::whereYear('created_at', $year)->count();
        // $pageViewsLast = PageView::whereYear('created_at', $lastYear)->count();

        // $pageViewsGrowth = $this->growthPercentage($pageViewsNow, $pageViewsLast);
        // $pageViewsExtra  = $pageViewsNow - $pageViewsLast;

        /* ================= USERS ================= */
        $usersNow  = User::where('role', '!=', 'admin')->whereYear('created_at', $year)->count();
        $usersLast = User::where('role', '!=', 'admin')->whereYear('created_at', $lastYear)->count();

        $usersGrowth = $this->growthPercentage($usersNow, $usersLast);
        $usersExtra  = $usersNow - $usersLast;

        /* ================= ORDERS ================= */
        $ordersNow = Order::whereHas(
            'payment',
            fn($q) =>
            $q->where('status', 'settlement')->whereYear('created_at', $year)
        )->count();

        $ordersLast = Order::whereHas(
            'payment',
            fn($q) =>
            $q->where('status', 'settlement')->whereYear('created_at', $lastYear)
        )->count();

        $ordersGrowth = $this->growthPercentage($ordersNow, $ordersLast);
        $ordersExtra  = $ordersNow - $ordersLast;

        /* ================= SALES ================= */
        $salesNow = Order::whereHas(
            'payment',
            fn($q) =>
            $q->where('status', 'settlement')->whereYear('created_at', $year)
        )->sum('total');

        $salesLast = Order::whereHas(
            'payment',
            fn($q) =>
            $q->where('status', 'settlement')->whereYear('created_at', $lastYear)
        )->sum('total');

        $salesGrowth = $this->growthPercentage($salesNow, $salesLast);
        $salesExtra  = $salesNow - $salesLast;


        $orders = Order::orderBy('id', 'desc')->get();

        return view('dashboard.index', compact(
            // 'pageViewsNow',
            // 'pageViewsGrowth',
            'usersNow',
            'usersGrowth',
            'usersExtra',
            'ordersNow',
            'ordersGrowth',
            'ordersExtra',
            'salesNow',
            'salesGrowth',
            'salesExtra',
            'orders'
        ));
    }

    public function logout()
    {
        Auth::logout();
        return redirect()->route('login');
    }
}
