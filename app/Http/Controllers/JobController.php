<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\JobFilterService;

class JobController extends Controller
{
    
    protected $jobFilterService;

    public function __construct(JobFilterService $jobFilterService)
    {
        $this->jobFilterService = $jobFilterService;
    }

    public function index(Request $request) {

        $perPage = $request->input('per_page', 10);

        // Apply filters and paginate results
        $jobs = $this->jobFilterService->applyFilters($request)
        ->with([
            'languages:id,name',
            'locations:id,city',
            'categories:id,name', // Fetch only id and name
            'jobAttributes.attribute'
        ])
        ->paginate($perPage);

        return response()->json($jobs);
    }
}
