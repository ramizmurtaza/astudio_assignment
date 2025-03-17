<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;

abstract class Controller
{
    /**
     * Returns a success response with the provided data.
     *
     * @param mixed $result
     * @param string $message
     * @return JsonResponse
     */
    public function sendSuccess($result, string $message): JsonResponse
    {
        $response = [
            'status' => true,
            'message' => $message,
            'data' => $result
        ];
        
        // Return JSON response with a 200 HTTP status code
        return response()->json($response, 200);
    }

    /**
     * Returns an error response with the provided error message.
     *
     * @param string $error
     * @param array $errorMessages
     * @return JsonResponse
     */
    public function sendError(string $error, array $errorMessages = []): JsonResponse
    {
        $response = [
            'status' => false,
            'message' => $error,
            'error' => $errorMessages,
            'data' => null
        ];
        
        // Return JSON response with a 500 HTTP status code for errors
        return response()->json($response, 500);
    }

    /**
     * Retrieves data with optional pagination.
     *
     * @param \Illuminate\Database\Eloquent\Builder|null $object
     * @param bool|null $pagination
     * @param int|null $perPage
     * @param int|null $page
     * @return mixed
     */
    public static function getData(
        $object = null,
        ?bool $pagination = null,
        ?int $perPage = null,
        ?int $page = null
    ) {
        if ($pagination) {
            return $object->paginate($perPage);
        }

        // If no pagination, return all results
        return $object->get();
    }
}
