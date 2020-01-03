<?php

return [
    /*
     * The name of the macro that is added to the Eloquent query builder.
     */
    'method_name' => 'jsonPaginate',

    /*
     * Here you can override the base url to be used in the link items.
     */
    'base_url' => null,

    /*
     * The name of the query parameter used for pagination
     */
    'pagination_parameter' => 'filters',

    /*
     * Offset
     * The key of the page[x] query string parameter for page number.
     */
    'number_parameter' => 'offset',

    /*
     * Limit
     * The key of the page[x] query string parameter for page size.
     */
    'size_parameter' => 'limit',

    /*
     * Default limit
     * The default number of results that will be returned
     * when using the JSON API paginator.
     */
    'default_size' => 10,

    /*
     * Max allowed limit
     * The maximum number of results that will be returned
     * when using the JSON API paginator.
     */
    'max_results' => 100,
];
