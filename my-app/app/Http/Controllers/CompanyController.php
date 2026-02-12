<?php

namespace App\Http\Controllers;

use App\Models\Company;
use Illuminate\Http\Request;

class CompanyController extends Controller
{
    /**
     * Get all companies for super admin
     */
    public function index(Request $request)
    {
        // Only SuperAdmin can view all companies
        if (!$request->user()->hasRole('SuperAdmin')) {
            abort(403, 'Unauthorized access');
        }

        $companies = Company::withCount(['users', 'shortUrls'])
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json($companies);
    }
}
