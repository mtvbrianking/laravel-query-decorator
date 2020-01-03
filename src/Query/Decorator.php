<?php

namespace Bmatovu\QueryDecorator\Query;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Builder;

class Decorator
{
    /**
     * Apply constraints to a query.
     *
     * @param  \Illuminate\Database\Eloquent\Builder $query
     * @param  array                                 $constraints
     * @param  array                                 $tableModelMap
     * @param  boolean                               $hasRelations
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public static function decorate(Builder $query, array $constraints, array $tableModelMap = [], bool $hasRelations = false): Builder
    {
        foreach ($constraints as $constraint => $options) {
            $applyConstraint = __NAMESPACE__ . "\Decorator::{$constraint}";

            if (!is_callable($applyConstraint)) {
                continue;
            }

            if (!$options) {
                continue;
            }

            $query = call_user_func_array($applyConstraint, [$query, $options, $tableModelMap, $hasRelations]);
        }

        return $query;
    }

    /**
     * Determine corresponding table field.
     *
     * @param  array  $modelField
     * @param  array  $tableModelMap
     *
     * @return string Table column
     */
    protected static function getTableField(string $modelField, array $tableModelMap): string
    {
        if (empty($tableModelMap)) {
            return $modelField;
        }

        $model = null;
        $field = $modelField;

        $modelFieldParts = explode('.', $modelField);

        if (count($modelFieldParts) > 1) {
            $model = $modelFieldParts[0];
            $field = $modelFieldParts[1];
        }

        $table = array_search($model, $tableModelMap);

        $tableField = "{$table}.{$field}";

        return $tableField;
    }

    /**
     * Map model fields to table columns.
     *
     * @param  array   $fields        Model fields
     * @param  array   $tableModelMap
     * @param  boolean $hasRelations
     *
     * @return array   Table columns
     */
    protected static function modelToTable(array $fields, array $tableModelMap, bool $hasRelations = false): array
    {
        if (empty($tableModelMap)) {
            return $fields;
        }

        foreach ($fields as &$field) {
            $model = null;

            $modelField = explode('.', $field);

            if (count($modelField) > 1) {
                $model = $modelField[0];
                $field = $modelField[1];
            }

            $table = array_search($model, $tableModelMap);

            $tableField = "{$table}.{$field}";

            $field = $hasRelations ? "{$tableField} AS {$tableField}" : $tableField;
        }

        return $fields;
    }

    /**
     * Format raw sql result set as eloquent model result.
     *
     * @param  \Illuminate\Database\Eloquent\Collection|array $resultSet
     * @param  array                                          $tableModelMap
     *
     * @return array
     */
    public static function resultsByModel($resultSet, array $tableModelMap): array
    {
        if ($resultSet instanceof Collection) {
            $resultSet = $resultSet->toArray();
        }

        $data = [];
        foreach ($resultSet as $row) {
            $datum = [];
            foreach ($row as $key => $value) {
                $tableField = explode('.', $key);

                // Without table.
                if (count($tableField) == 1) {
                    $field = $tableField[0];
                    $datum[$field] = $value;
                    continue;
                }

                $table = $tableField[0];
                $field = $tableField[1];

                $model = $tableModelMap[$table];

                if (is_null($model)) {
                    $datum[$field] = $value;
                    continue;
                }

                $datum[$model][$field] = $value;
            }
            $data[] = $datum;
        }

        return $data;
    }

    /**
     * Determine related models from columns asked.
     *
     * @param  array  $columns
     *
     * @return array
     */
    public static function getRelations(array $columns): array
    {
        $tables = [];

        foreach ($columns as $column) {
            $parts = explode('.', $column);

            $table = null;

            if (count($parts) > 1) {
                $table = $parts[0];
            }

            if (in_array($table, $tables)) {
                continue;
            }

            if ($table === null) {
                continue;
            }

            $tables[] = $table;
        }

        return $tables;
    }

    /**
     * Add all where clauses.
     *
     * @param  \Illuminate\Database\Eloquent\Builder $query
     * @param  array                                 $wheres
     * @param  array                                 $tableModelMap
     * @param  boolean                               $hasRelations
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public static function filter(Builder $query, array $wheres, array $tableModelMap = [], bool $hasRelations = false): Builder
    {
        $query->where(function ($query) use ($wheres, $tableModelMap, $hasRelations) {
            foreach ($wheres as $constraint => $options) {
                $applyConstraint = __NAMESPACE__ . "\Decorator::{$constraint}";

                if (!is_callable($applyConstraint)) {
                    continue;
                }

                $query = call_user_func_array($applyConstraint, [$query, $options, $tableModelMap, $hasRelations]);
            }
        });

        return $query;
    }

    /**
     * Join tables.
     *
     * @param  \Illuminate\Database\Eloquent\Builder $query
     * @param  array                                 $joins
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public static function join(Builder $query, array $joins): Builder
    {
        foreach ($joins as $join) {
            $query->join($join['table'], $join['first'], $join['operator'], $join['second'], $join['type']);
        }

        return $query;
    }

    /**
     * Choose the columns to be selected.
     *
     * @param  \Illuminate\Database\Eloquent\Builder $query
     * @param  array                                 $columns
     * @param  array                                 $tableModelMap
     * @param  boolean                               $hasRelations
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public static function select(Builder $query, array $columns, array $tableModelMap = [], bool $hasRelations = false): Builder
    {
        $columns = static::modelToTable($columns, $tableModelMap, $hasRelations);

        return $query->select($columns);
    }

    /**
     * Add a basic where clause to the query.
     *
     * @param  \Illuminate\Database\Eloquent\Builder $query
     * @param  array                                 $filters
     * @param  array                                 $tableModelMap
     * @param  boolean                               $hasRelations
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public static function where(Builder $query, array $filters, array $tableModelMap = [], bool $hasRelations = false): Builder
    {
        foreach ($filters as $filter) {
            $column = $hasRelations ? static::getTableField($filter['column'], $tableModelMap) : $filter['column'];

            $query->where($column, $filter['operator'], $filter['value'], $filter['boolean']);
        }

        return $query;
    }

    /**
     * Add a "where null" clause to the query.
     *
     * @param  \Illuminate\Database\Eloquent\Builder $query
     * @param  array                                 $filters
     * @param  array                                 $tableModelMap
     * @param  boolean                               $hasRelations
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public static function whereNull(Builder $query, array $filters, array $tableModelMap = [], bool $hasRelations = false): Builder
    {
        foreach ($filters as $filter) {
            $column = $hasRelations ? static::getTableField($filter['column'], $tableModelMap) : $filter['column'];

            $query->whereNull($column, $filter['boolean'], $filter['not']);
        }

        return $query;
    }

    /**
     * Add a "where in" clause to the query.
     *
     * @param  \Illuminate\Database\Eloquent\Builder $query
     * @param  array                                 $filters
     * @param  array                                 $tableModelMap
     * @param  boolean                               $hasRelations
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public static function whereIn(Builder $query, array $filters, array $tableModelMap = [], bool $hasRelations = false): Builder
    {
        foreach ($filters as $filter) {
            $column = $hasRelations ? static::getTableField($filter['column'], $tableModelMap) : $filter['column'];

            $query->whereIn($column, $filter['values'], $filter['boolean'], $filter['not']);
        }

        return $query;
    }

    /**
     * Add a "where between" clause to the query.
     *
     * @param  \Illuminate\Database\Eloquent\Builder $query
     * @param  array                                 $filters
     * @param  array                                 $tableModelMap
     * @param  boolean                               $hasRelations
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public static function whereBetween(Builder $query, array $filters, array $tableModelMap = [], bool $hasRelations = false): Builder
    {
        foreach ($filters as $filter) {
            $column = $hasRelations ? static::getTableField($filter['column'], $tableModelMap) : $filter['column'];

            $query->whereBetween($column, $filter['values'], $filter['boolean'], $filter['not']);
        }

        return $query;
    }

    /**
     * Add grouped filters.
     *
     * @param  \Illuminate\Database\Eloquent\Builder $query
     * @param  array                                 $groups
     * @param  array                                 $tableModelMap
     * @param  boolean                               $hasRelations
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public static function whereGrouped(Builder $query, array $groups, array $tableModelMap = [], bool $hasRelations = false): Builder
    {
        foreach ($groups as $group) {
            $query->where(function ($query) use ($group, $tableModelMap, $hasRelations) {
                foreach ($group as $constraint => $options) {
                    $applyConstraint = __NAMESPACE__ . "\Decorator::{$constraint}";

                    if (!is_callable($applyConstraint)) {
                        continue;
                    }

                    $query = call_user_func_array($applyConstraint, [$query, $options, $tableModelMap, $hasRelations]);
                }
            }, null, null, $group['boolean']);
        }

        return $query;
    }

    /**
     * Add an "order by" clause to the query.
     *
     * @param  \Illuminate\Database\Eloquent\Builder $query
     * @param  array                                 $orders
     * @param  array                                 $tableModelMap
     * @param  boolean                               $hasRelations
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public static function orderBy(Builder $query, array $orders, array $tableModelMap = [], bool $hasRelations = false): Builder
    {
        foreach ($orders as $order) {
            if (!in_array($order['direction'], ['asc', 'desc'], true)) {
                throw new InvalidArgumentException('Order direction must be "asc" or "desc".');
            }

            $column = $hasRelations ? static::getTableField($order['column'], $tableModelMap) : $order['column'];

            $query->orderBy($column, $order['direction']);
        }

        return $query;
    }

    /**
     * Choose the "offset" value of the query.
     *
     * Must set limit if offest is chosen.
     *
     * @see https://github.com/laravel/framework/issues/5458 #5458
     *
     * @param  \Illuminate\Database\Eloquent\Builder $query
     * @param  int                                   $value
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public static function offset(Builder $query, int $value): Builder
    {
        $query->offset(max(0, $value));

        $query->limit(0);

        return $query;
    }

    /**
     * Choose the "limit" value of the query.
     *
     * @param  \Illuminate\Database\Eloquent\Builder $query
     * @param  int                                   $value
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public static function limit(Builder $query, int $value): Builder
    {
        return $query->limit(max(0, $value));
    }
}
