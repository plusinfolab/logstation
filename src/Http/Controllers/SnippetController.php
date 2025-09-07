<?php

namespace PlusinfoLab\Logstation\Http\Controllers;

use Illuminate\Http\Request;
use PlusinfoLab\Logstation\Models\LogSnippet;

class SnippetController
{
    /**
     * Display a listing of snippets.
     */
    public function index(Request $request)
    {
        $snippets = LogSnippet::query()
            ->when(auth()->check(), function ($query) {
                $query->accessibleBy(auth()->id());
            })
            ->latest()
            ->get();

        return response()->json($snippets);
    }

    /**
     * Store a newly created snippet.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'filters' => 'required|array',
            'is_public' => 'boolean',
        ]);

        $validated['user_id'] = auth()->id();

        $snippet = LogSnippet::create($validated);

        return response()->json($snippet, 201);
    }

    /**
     * Display the specified snippet.
     */
    public function show(string $id)
    {
        $snippet = LogSnippet::findOrFail($id);

        // Check access
        if (!$snippet->is_public && $snippet->user_id !== auth()->id()) {
            abort(403, 'Unauthorized');
        }

        return response()->json($snippet);
    }

    /**
     * Update the specified snippet.
     */
    public function update(Request $request, string $id)
    {
        $snippet = LogSnippet::findOrFail($id);

        // Check ownership
        if ($snippet->user_id !== auth()->id()) {
            abort(403, 'Unauthorized');
        }

        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'filters' => 'sometimes|array',
            'is_public' => 'boolean',
        ]);

        $snippet->update($validated);

        return response()->json($snippet);
    }

    /**
     * Remove the specified snippet.
     */
    public function destroy(string $id)
    {
        $snippet = LogSnippet::findOrFail($id);

        // Check ownership
        if ($snippet->user_id !== auth()->id()) {
            abort(403, 'Unauthorized');
        }

        $snippet->delete();

        return response()->json(['message' => 'Snippet deleted successfully']);
    }
}
