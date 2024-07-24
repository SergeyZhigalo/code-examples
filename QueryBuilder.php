<?php

namespace Package\QueryBuilder;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Traits\ForwardsCalls;

/** @mixin Builder */
class ModelQueryBuilder
{
    use ForwardsCalls;

    private Builder $builder;

    private function __construct(Model $model)
    {
        $this->builder = $model->newQuery();
    }

    public static function query(Model $model): self
    {
        return new self($model);
    }

    public function byFieldLikeValue(string $field, ?string $value, bool $caseSensitive = true): self
    {
        return $this->when(
            $value,
            fn () => $this->where($field, $caseSensitive ? 'ilike' : 'like', '%'.$value.'%')
        );
    }

    public function byFieldComparisonValue(string $field, ?string $value, ?string $operator = '=', ?bool $customWhen = null): self
    {
        return $this->when(
            is_null($customWhen) ? $value : $customWhen,
            fn () => $this->where($field, $operator, $value)
        );
    }

    public function byFieldValue(
        string $field,
        ?string $value,
        string $operator = '=',
        bool $and = true,
        bool $negative = false
    ): self {
        return $this->when(
            $value,
            fn () => $this->unless(
                $negative,
                fn () => $this->where($field, $operator, $value, $and ? 'and' : 'or'),
                fn () => $this->whereNot($field, $operator, $value, $and ? 'and' : 'or')
            )
        );
    }

    public function byFieldWhereNull(string $field, ?bool $condition, ?bool $negative = false, bool $and = true): self
    {
        return $this->when(
            $condition,
            fn () => $this->whereNull($field, $and ? 'and' : 'or', $negative)
        );
    }

    public function byDateRange(string $field, ?string $from, ?string $to, bool $and = true, bool $negative = false): self
    {
        return $this->when(
            $from && $to,
            fn () => $this->whereBetween($field, [
                Carbon::parse($from)->startOfDay(),
                Carbon::parse($to)->endOfDay(),
            ], $and ? 'and' : 'or', $negative)
        );
    }

    public function byNestedRelation(string $relation, string $column, ?string $value): self
    {
        return $this->when(
            $relation && $column && $value,
            fn () => $this->whereHas($relation, fn ($query) => $query->where($column, $value))
        );
    }

    public function withOrderBy(?string $sort, ?string $direction): self
    {
        return $this->when(
            $sort,
            fn () => $this->orderBy($sort, $direction)
        );
    }

    public function byWhereIn(string $field, ?array $values)
    {
        return $this->when(
            $values,
            fn () => $this->whereIn($field, $values)
        );
    }

    public function byDateRangeWithTime(string $field, ?string $from, ?string $to, bool $and = true, bool $negative = false): self
    {
        return $this->when(
            $from && $to,
            fn () => $this->whereBetween($field, [
                Carbon::parse($from),
                Carbon::parse($to),
            ], $and ? 'and' : 'or', $negative)
        );
    }

    public function __call(string $name, array $arguments): mixed
    {
        return $this->forwardDecoratedCallTo(
            $this->builder,
            $name,
            $arguments,
        );
    }
}
