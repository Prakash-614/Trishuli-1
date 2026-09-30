<?php

namespace App\Http\Controllers;

use App\Services\ManualMentionService;
use Illuminate\Http\Request;

class ManualMentionController extends Controller
{
    public function create()
    {
        return view('mentions.add-manual');
    }

    public function preview(Request $request, ManualMentionService $service)
    {
        $request->validate(['url' => 'required|url']);

        if ($service->exists($request->url)) {
            return response()->json(['exists' => true]);
        }

        $preview = $service->fetchPreview($request->url);

        return response()->json([
            'exists' => false,
            'title' => $preview['title'],
            'snippet' => $preview['snippet'],
            'published_at' => $preview['published_at'],
            'title_may_be_just_a_name' => $preview['title_may_be_just_a_name'],
            'source' => $service->detectSource($request->url),
        ]);
    }

    public function store(Request $request, ManualMentionService $service)
    {
        $request->validate([
            'url' => 'required|url',
            'title' => 'required|string',
            'snippet' => 'required|string',
            'published_at' => 'nullable|string',
        ]);

        if ($service->exists($request->url)) {
            return redirect()->route('mentions.add-manual')->with('error', 'This URL is already in the database.');
        }

        $mention = $service->save(
            $request->url,
            $request->title,
            $request->snippet,
            $request->input('published_at')
        );

        $dateFormatted = $mention->mentioned_at ? $mention->mentioned_at->format('M d, Y H:i') : 'today';

        return redirect()->route('mentions.add-manual')->with('success', "Saved as mention #{$mention->id} (Published: {$dateFormatted}). It will be classified automatically within 15 minutes.");
    }
}