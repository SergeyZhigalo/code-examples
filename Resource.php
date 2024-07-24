<?php

namespace App\Http\Resources\Api\Examples;

use App\Http\Resources\Api\ExamplesAgreement\ExamplesAgreementResource;
use Foundation\Resources\BaseResource;

class ExamplesResource extends BaseResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->resource->id,
            'name' => $this->resource->name,
            'externalId' => $this->resource->external_id,
            'sort' => $this->resource->sort,
            'rating' => $this->resource->rating,
            'createdAt' => $this->resource->created_at,
            'updatedAt' => $this->resource->updated_at,
            'deletedAt' => $this->resource->deleted_at,
            'createdBy' => $this->resource->created_by,
            'updatedBy' => $this->resource->updated_by,

            'examples' => ExamplesResource::collection($this->whenLoaded('examples')),
        ];
    }
}
