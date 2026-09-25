<?php

namespace App\Http\Controllers;

use App\Models\AwarioMention;
use Illuminate\Http\Request;

class MentionReportController extends Controller
{
    public function index(Request $request)
    {
        $query = AwarioMention::query();

        if ($request->filled('date_from')) {
            $query->whereDate('mentioned_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('mentioned_at', '<=', $request->date_to);
        }
        if ($request->filled('risk_tier')) {
            $query->where('risk_tier', 'LIKE', $request->risk_tier . '%');
        }
        if ($request->filled('source')) {
            $query->where('source', $request->source);
        }
        if ($request->filled('sentiment')) {
            $query->where('sentiment', 'LIKE', $request->sentiment);
        }

        $perPage = 25;
        $mentions = $query->orderByDesc('mentioned_at')->paginate($perPage)->withQueryString();

        $sources = AwarioMention::select('source')->distinct()->pluck('source');
        $tiers = AwarioMention::select('risk_tier')->whereNotNull('risk_tier')->distinct()->pluck('risk_tier');

        return view('mentions.report', compact('mentions', 'sources', 'tiers'));
    }
}
