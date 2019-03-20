<?php

namespace App\Listeners\RiskManagement;

use DragonStudio\AmqpBroadcaster\Broadcasting\AmqpDriver;

use Interop\Amqp\{
    AmqpContext,
    AmqpTopic,
    AmqpQueue,
    AmqpMessage
};

use App\Events\RiskManagement\Risk;

use Illuminate\Events\Dispatcher;

class RiskDispatchSubscriber
{
    /**
     * @var AmqpDriver
     */
    private $driver;

    /**
     * @var AmqpContext
     */
    private $context;

    public function __construct(AmqpDriver $driver)
    {
        $this->setDriver($driver);
    }

    public function __destruct()
    {
        if ($this->context) {
            $this->context->close();
        }
    }

    public function setDriver(AmqpDriver $driver): self
    {
        $this->driver = $driver;

        return $this;
    }

    public function getDriver(): AmqpDriver
    {
        return $this->driver;
    }

    public function getContext(): AmqpContext
    {
        if (!$this->context) {
            $driver   = $this->getDriver();
            $settings = config('risks.connection', []);

            $this->context = $driver->createContext($settings);
        }

        return $this->context;
    }

    private function getPublishOptions(): array
    {
        $config = config('risks.consumer');

        return [
            'topic' => [
                'name'  => $config['topic']['name'],
                'type'  => AmqpTopic::TYPE_DIRECT,
                'flags' => AmqpTopic::FLAG_DURABLE
            ],

            'queue' => [
                'name'       => $config['queue']['name'],
                'flags'      => AmqpQueue::FLAG_DURABLE,
                'bindingKey' => $config['binding']['key']
            ],

            'message' => [
                'deliveryMode' => AmqpMessage::DELIVERY_MODE_PERSISTENT,
                'routingKey'   => null
            ]
        ];
    }

    private function notify($data)
    {
        $driver  = $this->getDriver();
        $context = $this->getContext();
        $options = $this->getPublishOptions();

        $driver->publish($context, $data, $options);
    }

    public function onRisk(Risk $event)
    {
        $data = [
            'risk'    => $event->code,
            'level'   => $event->level,
            'objects' => $event->objects
        ];

        $this->notify($data);
    }

    public function subscribe(Dispatcher $dispatcher)
    {
        $dispatcher->listen(
            Risk::class,
            [ $this, 'onRisk' ]
        );
    }
}
