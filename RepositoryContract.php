<?php

namespace App\Contracts\Repositories\Example;

use App\DTOs\FilterDTO;
use App\Models\Example;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

interface ExampleRepositoryContract
{
    public function getExamples(FilterDTO $filterDTO): LengthAwarePaginator;

    public function getExampleById(string $id): ?Example;

    public function createExample(array $data): ?Example;

    public function editExample(string $id, array $data): bool;

    public function deleteExample(string $id): array;

    public function getExamplesByCodes(array $codes): Collection;
}
