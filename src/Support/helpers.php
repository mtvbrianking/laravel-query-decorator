<?php

use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Arr;

if (! function_exists('array_get')) {
    /**
     * Get array value by key or default.
     *
     * @param array  $haystack The array
     * @param string $needle   The searched value
     * @param mixed  $default
     *
     * @return mixed
     */
    function array_get(array $haystack, $needle, $default = null)
    {
        $keys = explode('.', $needle);

        foreach ($keys as $idx => $needle) {
            if (! isset($haystack[$needle])) {
                return $default;
            }

            if ($idx === (sizeof($keys) - 1)) {
                return $haystack[$needle];
            }

            $haystack = $haystack[$needle];
        }

        return $default;
    }
}

if (! function_exists('paginate')) {
    /**
     * Paginate api dataset.
     *
     * @param \Illuminate\Http\Request $request
     * @param object                   $apiResponse
     *
     * @return \Illuminate\Pagination\LengthAwarePaginator
     */
    function paginate(Request $request, object $apiResponse): LengthAwarePaginator
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
