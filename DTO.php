<?php

namespace ExampleService\Client\DTOs;

use WendellAdriel\ValidatedDTO\SimpleDTO;

class ExampleAddToDialerDTO extends SimpleDTO
{
    public ?string $clientName = null;
    public ?string $orderNum = null;
    public ?string $phone = null;
    public ?string $siteId = null;

    protected function defaults(): array
    {
        return [
            'clientName' => null,
            'orderNum' => null,
            'phone' => null,
            'siteId' => null,
        ];
    }

    public function toRequestData(): array
    {
        return [
            'ClientName' => $this->clientName,
            'OrderNum' => $this->orderNum,
            'Phone' => $this->phone,
            'SiteId' => $this->siteId,
        ];
    }

    protected function casts(): array
    {
        return [];
    }
}
