<?php

namespace App\Http\Controllers\Api;

use App\Actions\Job\DeleteJob;
use App\Actions\Job\ShowJob;
use App\Actions\Job\StoreJob;
use App\Http\Controllers\Controller;
use App\Http\Requests\Job\StoreRequest;
use App\Http\Resources\Job\ScrapeResults;
use Illuminate\Http\JsonResponse;

class JobController extends Controller
{
    public function store(StoreRequest $request, StoreJob $action): JsonResponse
    {
        $jobId = $action->handle($request->validated());

        return response()->json(['message' => 'success', 'job_id' => $jobId]);
    }

    public function show(string $id, ShowJob $action): ScrapeResults|JsonResponse
    {
        try {
            $data = $action->handle($id);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 404);
        }

        return new ScrapeResults($data);
    }

    public function delete(string $id, DeleteJob $action): JsonResponse
    {
        try {
            $action->handle($id);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 404);
        }

        return response()->json(['message' => 'success']);
    }
}
