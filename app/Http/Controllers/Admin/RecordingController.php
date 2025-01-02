<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Recording;
use Illuminate\Http\Request;

class RecordingController extends Controller
{
    public function index(Request $request)
    {
        $query = Recording::query();

        // Handle search
        if ($request->has('search')) {
            $searchTerm = $request->search;
            $query->where(function ($q) use ($searchTerm) {
                $q->where('call_sid', 'like', "%{$searchTerm}%")
                    ->orWhere('recording_sid', 'like', "%{$searchTerm}%");
            });
        }

        // Handle sorting
        if ($request->has('sortField') && $request->has('sortOrder')) {
            $query->orderBy($request->sortField, $request->sortOrder === 'ascend' ? 'asc' : 'desc');
        } else {
            $query->orderBy('created_at', 'desc');
        }

        // Handle pagination
        $perPage = $request->input('limit', 10);
        $recordings = $query->paginate($perPage);

        return response()->json([
            'data' => $recordings->items(),
            'total' => $recordings->total(),
            'current_page' => $recordings->currentPage(),
            'per_page' => $recordings->perPage(),
        ]);
    }

    public function show($id)
    {
        $recording = Recording::findOrFail($id);
        return response()->json($recording);
    }

    public function destroy($id)
    {
        $recording = Recording::findOrFail($id);
        $recording->delete();
        return response()->json(['message' => 'Recording deleted successfully']);
    }
}
