<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Link;
use App\Models\Click;
use Illuminate\Support\Facades\DB;

class StatisticsController extends Controller
{
    public function index(Request $request)
    {
        $dateRange = $this->getDateRange($request);

        $totalLinks = auth()->user()->links()
            ->whereBetween('created_at', [$dateRange['start'], $dateRange['end']])
            ->count();

        $totalClicks = auth()->user()->clicks()
            ->whereBetween('clicks.created_at', [$dateRange['start'], $dateRange['end']])
            ->count();

        $linkCreationsData = auth()->user()->links()
            ->whereBetween('links.created_at', [$dateRange['start'], $dateRange['end']])
            ->groupBy(DB::raw('DATE(links.created_at)'))
            ->orderBy('date', 'asc')
            ->get([
                DB::raw('DATE(links.created_at) as date'),
                DB::raw('count(*) as count')
            ])
            ->pluck('count', 'date');

        $clicksData = auth()->user()->links()->with(['clicks' => function ($query) use ($dateRange) {
            $query->whereBetween('created_at', [$dateRange['start'], $dateRange['end']]);
        }])->get()->flatMap(function ($link) {
            return $link->clicks;
        })->groupBy(function ($click) {
            return $click->created_at->format('Y-m-d');
        })->map(function ($day) {
            return $day->count();
        });

        $period = new \DatePeriod(
            new \DateTime($dateRange['start_date']),
            new \DateInterval('P1D'),
            new \DateTime($dateRange['end_date'] . ' +1 day')
        );

        $dateRangeArray = [];
        foreach ($period as $key => $value) {
            $dateRangeArray[$value->format('Y-m-d')] = 0;
        }

        $linkCreations = collect($dateRangeArray)->merge($linkCreationsData);
        $clicks = collect($dateRangeArray)->merge($clicksData);

        $topLinks = auth()->user()->links()
            ->withCount(['clicks' => function ($query) use ($dateRange) {
                $query->whereBetween('clicks.created_at', [$dateRange['start'], $dateRange['end']]);
            }])
            ->orderBy('clicks_count', 'desc')
            ->take(5)
            ->get();

        $locations = DB::table('clicks')
            ->join('links', 'links.id', '=', 'clicks.link_id')
            ->where('links.user_id', auth()->id())
            ->whereBetween('clicks.created_at', [$dateRange['start'], $dateRange['end']])
            ->whereNotNull('clicks.location')
            ->select('clicks.location', DB::raw('count(*) as total'))
            ->groupBy('clicks.location')
            ->orderBy('total', 'desc')
            ->get();

        $browsers = DB::table('clicks')
            ->join('links', 'links.id', '=', 'clicks.link_id')
            ->where('links.user_id', auth()->id())
            ->whereBetween('clicks.created_at', [$dateRange['start'], $dateRange['end']])
            ->whereNotNull('clicks.browser')
            ->select('clicks.browser', DB::raw('count(*) as total'))
            ->groupBy('clicks.browser')
            ->orderBy('total', 'desc')
            ->get();

        $devices = DB::table('clicks')
            ->join('links', 'links.id', '=', 'clicks.link_id')
            ->where('links.user_id', auth()->id())
            ->whereBetween('clicks.created_at', [$dateRange['start'], $dateRange['end']])
            ->whereNotNull('clicks.os')
            ->select('clicks.os', DB::raw('count(*) as total'))
            ->groupBy('clicks.os')
            ->orderBy('total', 'desc')
            ->get();

        return view('statistics.index', compact(
            'totalLinks',
            'totalClicks',
            'linkCreations',
            'clicks',
            'topLinks',
            'locations',
            'browsers',
            'devices'
        ));
    }

    private function getDateRange(Request $request)
    {
        $dateRange = $request->date_range ?? now()->subDays(29)->format('Y-m-d') . ' to ' . now()->format('Y-m-d');
        $dates = explode(' to ', $dateRange);
        $startDate = $dates[0];
        $endDate = $dates[1] ?? $dates[0];

        return [
            'start' => $startDate . ' 00:00:00',
            'end' => $endDate . ' 23:59:59',
            'start_date' => $startDate,
            'end_date' => $endDate,
        ];
    }
}
