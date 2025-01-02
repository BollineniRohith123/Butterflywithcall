<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Recording;
use Illuminate\Http\Request;

class RecordingController extends Controller
{
    public function index(Request $request)
    {
        $query = Recording::query();

        // Handle search
        if ($request->filled('search')) {
            $searchTerm = $request->search;
            $query->where(function ($q) use ($searchTerm) {
                $q->where('call_sid', 'like', "%{$searchTerm}%")
                    ->orWhere('recording_sid', 'like', "%{$searchTerm}%")
                    ->orWhere('phone_number', 'like', "%{$searchTerm}%");
            });
        }

        // Handle sorting
        if ($request->filled('sortField') && $request->filled('sortOrder')) {
            $sortField = $request->sortField;
            $sortOrder = $request->sortOrder === 'ascend' ? 'asc' : 'desc';
            $query->orderBy($sortField, $sortOrder);
        } else {
            $query->orderBy('created_at', 'desc');
        }

        // Handle pagination
        $page = max(1, (int)$request->input('page', 1));
        $perPage = max(1, (int)$request->input('limit', 10));
        
        $total = $query->count();
        $recordings = $query->offset(($page - 1) * $perPage)
                          ->limit($perPage)
                          ->get();

        // Format duration for display
        $recordings->transform(function ($recording) {
            $recording->formatted_duration = $recording->recording_duration ? 
                gmdate('H:i:s', $recording->recording_duration) : 
                '00:00:00';
            return $recording;
        });

        return response()->json([
            'success' => true,
            'data' => [
                'list' => $recordings,
                'meta' => [
                    'total' => $total,
                    'current_page' => $page,
                    'per_page' => $perPage,
                    'total_pages' => ceil($total / $perPage)
                ]
            ]
        ]);
    }

    public function show($id)
    {
        $recording = Recording::find($id);
        
        if (!$recording) {
            return response()->json([
                'success' => false,
                'message' => 'Recording not found'
            ], 404);
        }

        $recording->formatted_duration = $recording->recording_duration ? 
            gmdate('H:i:s', $recording->recording_duration) : 
            '00:00:00';

        return response()->json([
            'success' => true,
            'data' => $recording
        ]);
    }

    public function destroy($id)
    {
        $recording = Recording::find($id);
        
        if (!$recording) {
            return response()->json([
                'success' => false,
                'message' => 'Recording not found'
            ], 404);
        }

        $recording->delete();

        return response()->json([
            'success' => true,
            'message' => 'Recording deleted successfully'
        ]);
    }
}
