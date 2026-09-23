<?php

namespace App\Http\Controllers;

use App\Models\AwarioMention;
use Illuminate\Support\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        $tierCounts = AwarioMention::selectRaw('risk_tier, count(*) as total')
            ->whereNotNull('risk_tier')
            ->groupBy('risk_tier')
            ->pluck('total', 'risk_tier');

        $red = $tierCounts->filter(fn($v, $k) => str_starts_with($k, 'RED'))->sum();
        $yellowHigh = $tierCounts->filter(fn($v, $k) => str_starts_with($k, 'YELLOW') && str_contains($k, 'HIGH'))->sum();
        $yellow = $tierCounts->filter(fn($v, $k) => str_starts_with($k, 'YELLOW'))->sum();
        $green = $tierCounts->filter(fn($v, $k) => str_starts_with($k, 'GREEN'))->sum();

        $overallTier = $red > 0 ? 'RED' : ($yellowHigh > 0 ? 'YELLOW — HIGH' : ($yellow > 0 ? 'YELLOW' : 'GREEN'));

        // Priority items requiring immediate action
        $priorityItems = AwarioMention::where(function ($q) {
            $q->where('risk_tier', 'LIKE', 'RED%')
                ->orWhere('risk_tier', 'LIKE', '%HIGH%');
        })
            ->orderByDesc('mentioned_at')
            ->limit(6)
            ->get();

        // 14-day trend aggregation
        $trend = AwarioMention::selectRaw('DATE(mentioned_at) as day, risk_tier, count(*) as total')
            ->whereNotNull('risk_tier')
            ->where('mentioned_at', '>=', Carbon::now()->subDays(14))
            ->groupBy('day', 'risk_tier')
            ->orderBy('day')
            ->get()
            ->groupBy('day');

        $trendLabels = $trend->keys()->map(fn($d) => Carbon::parse($d)->format('M d'));
        $trendRed = $trend->keys()->map(fn($day) => $trend[$day]->filter(fn($r) => str_starts_with($r->risk_tier, 'RED'))->sum('total'));
        $trendYellow = $trend->keys()->map(fn($day) => $trend[$day]->filter(fn($r) => str_starts_with($r->risk_tier, 'YELLOW'))->sum('total'));
        $trendGreen = $trend->keys()->map(fn($day) => $trend[$day]->filter(fn($r) => str_starts_with($r->risk_tier, 'GREEN'))->sum('total'));

        // Summary KPIs
        $totalMentions = AwarioMention::count();
        $totalReach = AwarioMention::sum('reach') ?? 0;
        $lastSync = AwarioMention::max('updated_at');

        // Source distribution
        $sourceStats = AwarioMention::selectRaw('source, count(*) as count')
            ->whereNotNull('source')
            ->groupBy('source')
            ->orderByDesc('count')
            ->limit(5)
            ->get();

        // Latest general mentions stream (5 most recent)
        $recentMentions = AwarioMention::orderByDesc('mentioned_at')->limit(5)->get();

        return view('dashboard', compact(
            'red',
            'yellowHigh',
            'yellow',
            'green',
            'overallTier',
            'priorityItems',
            'trendLabels',
            'trendRed',
            'trendYellow',
            'trendGreen',
            'totalMentions',
            'totalReach',
            'lastSync',
            'sourceStats',
            'recentMentions'
        ));
    }
}
