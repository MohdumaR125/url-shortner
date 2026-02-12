<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class TeamController extends Controller
{
    /**
     * Get team members for the current user's company (Admin only)
     */
    public function index(Request $request)
    {
        $user = $request->user();

        if (!$user->hasRole('Admin')) {
            abort(403, 'Only admins can view team members');
        }

        $teamMembers = User::where('company_id', $user->company_id)
            ->withCount('shortUrls')
            ->with('roles')
            ->orderBy('created_at', 'desc')
            ->get();

        $formatted = $teamMembers->map(function ($member) {
            return [
                'id' => $member->id,
                'name' => $member->name,
                'email' => $member->email,
                'role' => $member->roles->first()?->name ?? 'N/A',
                'urls_count' => $member->short_urls_count,
                'created_at' => $member->created_at,
            ];
        });

        return response()->json(['data' => $formatted]);
    }
}
