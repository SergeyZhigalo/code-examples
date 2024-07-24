<?php

namespace App\Jobs;

use App\Models\BaseModel;
use Foundation\Context\BindContext;
use Foundation\Exceptions\BaseException;
use Kafka\Contracts\ModelKafkaPublisherContract;
use Kafka\Enums\TopicEnum;
use ExampleService\Client\Contracts\ExampleClientContract;
use ExampleService\Client\DTOs\ExampleAddToDailerDTO;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Throwable;

class OrderSendJob implements ShouldQueue
{
    use BindContext;
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;

    public function __construct(
        protected array $data = [],
    ) {
    }

    protected function getQueue(): ?string
    {
        return null;
    }

    public function tries(): int
    {
        return 3;
    }

    public function backoff(): array
    {
        return [3, 60, 300];
    }

    /**
     * @throws Throwable
     * @throws BaseException
     */
    public function handle(ExampleClientContract $exampleClient, ModelKafkaPublisherContract $kafkaPublisher): void
    {
        $model = Arr::get($this->data, 'model');

        $clientName = Arr::get($model, 'buyer_name');
        $orderNum = Arr::get($model, 'order_number');
        $phone = Arr::get($model, 'buyer_phone');
        $siteId = Arr::get($model, 'id');

        if (is_null($clientName) || is_null($orderNum) || is_null($phone) || is_null($siteId)) {
            throw new BaseException('Отсутствует обязательный параметр для отпарвки в Example');
        }

        $dto = ExampleAddToDailerDTO::fromArray([
            'clientName' => $clientName,
            'orderNum' => $orderNum,
            'phone' => $phone,
            'siteId' => $siteId,
        ]);

        try {
            $this->bindContext(Str::uuid()->toString(), 'example-service', ['process' => $this->name()]);

            Log::info('Начинаем обработку задачи в очереди ' . $this->name() . '. Попытка: ' . $this->attempt(), ['job' => $this->formatJobInfo()]);

            $response = $exampleClient->sendOrderRenewal($dto);

            $kafkaPublisher->publish(
                topic: ExampleEnum::ExampleSend->value,
                model: new BaseModel([
                    'id' => $siteId,
                    'sendingStatus' => empty(Arr::get($response, 'Error')),
                ])
            );

            Log::info('Обработка задачи выполнена успешно в очереди ' . $this->name() . '. Попытка: ' . $this->attempt(), ['job' => $this->formatJobInfo()]);
        } catch (Throwable $exception) {
            Log::error('Возникла ошибка при обработке задачи в очереди: ' . $this->name() . '. Попытка: ' . $this->attempt(), ['job' => $this->formatJobInfo(), 'exception' => $exception]);

            throw $exception;
        }
    }

    public function failed(Throwable $exception): void
    {
        Log::error('Не удалось обработать задачу в очереди ' . $this->name(), ['exception' => $exception]);
    }

    private function formatJobInfo(): array
    {
        return [
            'uuid' => $this->job?->uuid(),
            'backoff' => $this->job?->backoff(),
        ];
    }

    private function attempt(): int
    {
        return $this->job?->attempts();
    }

    private function name(): string
    {
        return $this->job?->getName() ?: static::class;
    }
}
