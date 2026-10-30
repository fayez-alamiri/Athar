<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Throwable;

class HealthController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        $startedAt = microtime(true);
        $databaseStatus = 'not_checked';

        if ($request->boolean('db')) {
            try {
                DB::select('SELECT 1');
                $databaseStatus = 'ok';
            } catch (Throwable $exception) {
                report($exception);
                $databaseStatus = 'error';
            }
        }

        $duration = round((microtime(true) - $startedAt) * 1000, 2);

        return response()->json([
            'status' => $databaseStatus === 'error' ? 'degraded' : 'ok',
            'database' => $databaseStatus,
            'duration_ms' => $duration,
        ], $databaseStatus === 'error' ? 503 : 200)->header('Cache-Control', 'no-store');
    }
}
