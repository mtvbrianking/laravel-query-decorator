<?php

namespace Bmatovu\QueryDecorator\Support;

class Datatable
{
    /**
     * Build query constraints from jQuery datatable request.
     *
     * @see https://github.com/DataTables/DataTables/blob/master/examples/server_side/scripts/ssp.class.php Example
     *
     * @param  array  $params         Request query parameters.
     * @param  string $searchOperator
     *
     * @return array                  Constraints
     */
    public static function buildConstraints(array $params, string $searchOperator = 'ilike'): array
    {
        $constraints = [];

        foreach ($params['columns'] as $col) {
            // Select

            if (!isset($col['name'])) {
                continue;
            }

            $constraints['select'][] = $col['name'];

            // Search

            if (!(bool) $col['searchable']) {
                continue;
            }

            // Column search

            if ($term = array_get($col['search'], 'value')) {
                $constraints['filter']['where'][] = [
                    'boolean' => 'and',
                    'column' => $col['name'],
                    'operator' => $searchOperator,
                    'value' => "%{$term}%",
                ];
            }

            // Global search

            if ($term = array_get($params['search'], 'value')) {
                $constraints['filter']['whereGrouped'][0]['boolean'] = 'and';
                $constraints['filter']['whereGrouped'][0]['where'][] = [
                    'boolean' => 'or',
                    'column' => $col['name'],
                    'operator' => $searchOperator,
                    'value' => "%{$term}%",
                ];
            }

            continue;
        }

        // Sort
        $constraints['orderBy'] = static::sorting($params);

        // Paginate
        $constraints['offset'] = (int) $params['start'];

        // Limit
        $constraints['limit'] = (int) $params['length'];

        // Draw
        $constraints['draw'] = isset($params['draw']) ? (int) $params['draw'] : 0;

        return $constraints;
    }

    /**
     * Build sorting options.
     *
     * @param  array $params
     *
     * @return array
     */
    public static function sorting(array $params): array
    {
        $orderBy = [];

        foreach ($params['order'] as $order) {
            $colIdx = $order['column'];

            $col = $params['columns'][$colIdx];

            if (!$col['orderable']) {
                continue;
            }

            if (!isset($col['name'])) {
                continue;
            }

            $orderBy[] = [
                'column' => $col['name'],
                'direction' => $order['dir'],
            ];
        }

        return $orderBy;
    }
}
