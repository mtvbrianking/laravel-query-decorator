<?php

namespace Bmatovu\QueryDecorator\Support;

use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Arr;

class Paginator
{
    /**
     * Paginate api api dataset.
     *
     * @param \Illuminate\Http\Request $request
     * @param object                   $apiResponse
     *
     * @return \Illuminate\Pagination\LengthAwarePaginator
     */
    public static function paginate(Request $request, object $apiResponse): LengthAwarePaginator
    {
        $options = [
            'path' => $request->url(),
            'pageName' => 'page',
        ];

        $paginator = new LengthAwarePaginator(
            $apiResponse->data,
            $apiResponse->total,
            $apiResponse->per_page,
            $apiResponse->current_page,
            $options
        );

        return $paginator->appends(Arr::except($request->input(), '_token'));
    }
}
