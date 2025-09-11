<?php

namespace App\Http\Controllers;

use App\Models\Link;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $totalLinks = auth()->user()->links()->count();
        $totalClicks = auth()->user()->links()->sum('visits');

        $dateRange = $request->date_range ?? now()->subDays(6)->format('Y-m-d') . ' to ' . now()->format('Y-m-d');
        $dates = explode(' to ', $dateRange);
        $startDate = $dates[0];
        $endDate = $dates[1] ?? $dates[0];

        $query = auth()->user()->links()->with(['clicks' => function ($query) use ($startDate, $endDate) {
            $query->whereBetween('created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59']);
        }]);

        $links = $query->get();

        $dailyClicksData = $links->flatMap(function ($link) {
            return $link->clicks;
        })->groupBy(function ($click) {
            return $click->created_at->format('Y-m-d');
        })->map(function ($day) {
            return $day->count();
        });

        $period = new \DatePeriod(
            new \DateTime($startDate),
            new \DateInterval('P1D'),
            new \DateTime($endDate . ' +1 day')
        );

        $dateRangeArray = [];
        foreach ($period as $key => $value) {
            $dateRangeArray[$value->format('Y-m-d')] = 0;
        }

        $dailyClicks = collect($dateRangeArray)->merge($dailyClicksData);

        return view('dashboard', compact('totalLinks', 'totalClicks', 'dailyClicks'));
    }
}
