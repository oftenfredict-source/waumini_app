<?php

namespace App\Http\Controllers\Api\Pastor;

use App\Http\Controllers\Controller;
use App\Models\Church;
use App\Models\User;
use App\Support\ApiResponse;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;

abstract class PastorController extends Controller
{
    protected function pastor(): User
    {
        return request()->user();
    }

    protected function church(): Church
    {
        return $this->pastor()->church;
    }

    protected function perPage(Request $request): int
    {
        $perPage = $request->integer('per_page') ?: 20;

        return min(100, max(1, $perPage));
    }

    protected function paginated(LengthAwarePaginator $paginator, mixed $data): JsonResponse
    {
        return ApiResponse::paginated($paginator, $data);
    }

    /**
     * @param  Builder<\Illuminate\Database\Eloquent\Model>  $query
     * @param  list<string>  $columns
     */
    protected function searchColumns(Builder $query, Request $request, array $columns): void
    {
        $search = trim($request->string('search')->toString());

        if ($search === '' || $columns === []) {
            return;
        }

        $query->where(function (Builder $inner) use ($search, $columns) {
            foreach ($columns as $column) {
                $inner->orWhere($column, 'like', '%'.$search.'%');
            }
        });
    }
}
