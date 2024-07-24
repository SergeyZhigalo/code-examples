<?php

namespace App\Console\Commands\Consumers;

use App\Jobs\ExampleJob;
use Foundation\Context\BindContext;
use Kafka\Committer\KafkaCommitterFactory;
use Kafka\Enums\TopicEnum;
use Carbon\Exceptions\Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Junges\Kafka\Contracts\ConsumerMessage;
use Junges\Kafka\Exceptions\ConsumerException;
use Junges\Kafka\Facades\Kafka;
use Monolog\Handler\StreamHandler;

class RequestOrderCreate extends Command
{
    use BindContext;

    protected $signature = 'consumer:example';

    protected $description = 'Запуск слушателя для отправки сообщений';

    /**
     * @throws Exception
     * @throws ConsumerException
     */
    public function handle(): void
    {
        if ($this->option('verbose')) {
            Log::getLogger()->pushHandler(new StreamHandler('php://stdout'));
        }

        $consumer = Kafka::consumer([TopicEnum::RequestOrderCreate->value])
            ->withAutoCommit(false)
            ->usingCommitterFactory(new KafkaCommitterFactory())
            ->withHandler(function (ConsumerMessage $message) {
                $key = $message->getKey();
                Log::debug("Поставка сообщения из топика: {$message->getTopicName()} с ключом $key", ['data' => $message->getBody()]);

                ExampleJob::dispatch(orderData: $message->getBody());

                Log::debug("Успешное завершение из топика: {$message->getTopicName()} и ключом $key");
            })
            ->build();

        $consumer->consume();
    }
}
