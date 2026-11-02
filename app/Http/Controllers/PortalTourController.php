<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class PortalTourController extends Controller
{
    /**
     * Mark a specific portal tour as completed for the authenticated user.
     */
    public function complete(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'portal' => ['required', 'string', Rule::in(['student', 'instructor', 'admin', 'parent', 'contributor'])],
        ]);

        $user = $request->user();

        if ($user) {
            $user->completeTour($validated['portal']);
        }

        return response()->json([
            'success' => true,
            'portal' => $validated['portal'],
            'completed' => true,
        ]);
    }

    /**
     * Reset a specific portal tour so it can be automatically or manually triggered again.
     */
    public function reset(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'portal' => ['required', 'string', Rule::in(['student', 'instructor', 'admin', 'parent', 'contributor'])],
        ]);

        $user = $request->user();

        if ($user) {
            $user->resetTour($validated['portal']);
        }

        return response()->json([
            'success' => true,
            'portal' => $validated['portal'],
            'completed' => false,
        ]);
    }
}

