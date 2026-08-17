<?php

namespace App\Http\Controllers\Dashboard;

use App\Domain\Dashboard\UseCases\ShowDashboardAnalyticsUseCase;
use App\Http\Controllers\Controller;
use App\Http\Responses\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ShowDashboardAnalyticsController extends Controller
{
    public function __construct(private ShowDashboardAnalyticsUseCase $useCase) {}

    public function __invoke(Request $request)
    {
        $filters = $request->validate([
            'period' => ['sometimes', 'string', Rule::in(['today', 'week', 'month', 'quarter', 'year'])],
            'from' => ['sometimes', 'date'],
            'to' => ['sometimes', 'date', 'after_or_equal:from'],
        ]);

        return ApiResponse::success($this->useCase->run($filters));
    }
}
