<?php

namespace App\Repositories\Example;

use App\Contracts\Repositories\Example\ExampleRepositoryContract;
use App\DTOs\FilterDTO;
use App\Models\Example;
use App\Repositories\BaseRepository;
use QueryBuilder\ModelQueryBuilder;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

final class ExampleRepository extends BaseRepository implements ExampleRepositoryContract
{
    public function getModel(): Model
    {
        return new Example();
    }

    public function getExamples(FilterDTO $filterDTO): LengthAwarePaginator
    {
        return ModelQueryBuilder::query($this->getModel())
            ->byFieldComparisonValue('id', $filterDTO->getId())
            ->withOrderBy($filterDTO->getSort(), $filterDTO->getDirection())
            ->withTrashed(!$filterDTO->getWithTrashed())
            ->paginate($filterDTO->getPerPage(), ['*'], 'page', $filterDTO->getPage());
    }

    public function getExampleById(string $id): ?Example
    {
        return $this->getModel()->newQuery()
            ->withTrashed()
            ->where("id", $id)
            ->first();
    }

    public function createExample(array $data): ?Example
    {
        return $this->getModel()->newQuery()
            ->create($data);
    }

    public function editExample(string $id, array $data): bool
    {
        return $this->getModel()->newQuery()
            ->where('id', $id)
            ->update($data);
    }

    public function deleteExample(string $id): array
    {
        $example = $this->getModel()->newQuery()
            ->withTrashed()
            ->find($id);

        $action = $example->trashed() ? 'restore' : 'delete';

        return [
            'operation' => $action,
            'status' => !is_null($example) && $example->{$action}(),
            'model' => $example,
        ];
    }

    public function getExamplesByCodes(array $codes): Collection
    {
        return $this->getModel()->newQuery()
            ->withTrashed()
            ->whereIn('code', $codes)
            ->get();
    }
}
