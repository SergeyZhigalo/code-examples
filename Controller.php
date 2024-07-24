<?php

namespace App\Http\Controllers\Api;

use App\Contracts\Services\ExampleEntity\ExampleEntityServiceContract;
use App\DTOs\FilterDTO;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\ExampleEntity\CreateExampleEntityRequest;
use App\Http\Requests\Api\ExampleEntity\GetCountriesRequest;
use App\Http\Requests\Api\ExampleEntity\GetCountriesByCodeRequest;
use App\Http\Requests\Api\ExampleEntity\UpdateExampleEntityRequest;
use App\Http\Resources\Api\ExampleEntity\ExampleEntityAllResource;
use App\Http\Resources\Api\ExampleEntity\ExampleEntityResource;
use App\Http\Resources\Api\ExampleEntity\DeleteExampleEntityResource;
use Foundation\Presenters\BasePresenter;
use Illuminate\Http\Response;
use WendellAdriel\ValidatedDTO\Exceptions\CastTargetException;
use WendellAdriel\ValidatedDTO\Exceptions\MissingCastTypeException;

final class ExampleEntityController extends Controller
{
    /**
     * @param ExampleEntityServiceContract $ExampleEntityService
     */
    public function __construct(
        private readonly ExampleEntityServiceContract $ExampleEntityService
    ) {
    }

    /**
     * @param GetCountriesRequest $request
     * @return Response
     * @throws CastTargetException
     * @throws MissingCastTypeException
     */
    public function index(GetCountriesRequest $request): Response
    {
        $countries = $this->ExampleEntityService->getCountries(
            new FilterDTO(
                [
                    'id' => $request->validated('filters.id'),
                    'sort' => $request->validated('sort', ''),
                    'withTrashed' => $request->validated('filters.withTrashed'),
                    'page' => $request->validated('page', '1'),
                    'perPage' => $request->validated('filters.perPage', '30'),
                ]
            )
        );

        return (new BasePresenter())->paginated(
            data: $countries,
            resourceNamespace: ExampleEntityResource::class
        );
    }

    /**
     * @param string $id
     * @return Response
     */
    public function show(string $id): Response
    {
        $ExampleEntity = $this->ExampleEntityService->getExampleEntityById($id);

        return (new BasePresenter())->success(
            data: ExampleEntityResource::make($ExampleEntity),
        );
    }

    /**
     * @param CreateExampleEntityRequest $request
     * @return Response
     */
    public function create(CreateExampleEntityRequest $request): Response
    {
        $ExampleEntity = $this->ExampleEntityService->createExampleEntity($request->validated());

        return (new BasePresenter())->success(
            data: ExampleEntityResource::make($ExampleEntity),
        );
    }

    /**
     * @param UpdateExampleEntityRequest $request
     * @param string $id
     * @return Response
     */
    public function update(UpdateExampleEntityRequest $request, string $id): Response
    {
        $ExampleEntity = $this->ExampleEntityService->editExampleEntity($id, $request->validated());

        return (new BasePresenter())->success(
            data: ExampleEntityResource::make($ExampleEntity),
        );
    }

    /**
     * @group 
     * @param string $id
     * @return Response
     */
    public function destroy(string $id): Response
    {
        $ExampleEntity = $this->ExampleEntityService->deleteExampleEntity($id);

        return (new BasePresenter())->success(
            data: DeleteExampleEntityResource::make($ExampleEntity),
        );
    }

    /**
     * @group 
     * @param GetCountriesByCodeRequest $request
     * @return Response
     */
    public function getCountriesByCodes(GetCountriesByCodeRequest $request): Response
    {
        $countries = $this->ExampleEntityService->getCountriesByCodes($request->validated('countriesCodes'));

        return (new BasePresenter())->success(
            data: ExampleEntityResource::collection($countries),
        );
    }
}
