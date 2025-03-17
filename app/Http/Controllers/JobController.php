<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\JobFilterService;
use Illuminate\Http\JsonResponse;

class JobController extends Controller
{
    /**
     * @var JobFilterService
     */
    protected JobFilterService $jobFilterService;

    /**
     * JobController Constructor.
     *
     * @param JobFilterService $jobFilterService
     */
    public function __construct(JobFilterService $jobFilterService)
    {
        $this->jobFilterService = $jobFilterService;
    }

    /**
     * Retrieves job listings with applied filters and pagination.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        // Apply filters to the job query
        $query = $this->jobFilterService->applyFilters($request)
            ->with([
                'languages:id,name',
                'locations:id,city',
                'categories:id,name',
                'jobAttributes.attribute'
            ]);

        // Apply pagination and retrieve the data
        $data = $this->getData(
            $query,
            $request->input('pagination', true), // Default to true if not provided
            $request->input('per_page', 10), // Default per_page value
            $request->input('page', 1) // Default page value
        );

        // Return a successful JSON response
        return $this->sendSuccess($data, config('messages.success'));
    }
}
