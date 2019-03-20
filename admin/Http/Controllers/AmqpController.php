<?php

namespace Admin\Http\Controllers;

use DragonStudio\AmqpBroadcaster\Broadcasting\AmqpDriver;
use Interop\Amqp\{
    AmqpMessage, AmqpQueue, AmqpTopic
};

class AmqpController extends Controller
{
    public static function recountRound($data, $type)
    {
        $options = [
            'topic' => [
                'name'  => "bet_processor.{$type}.pending.rounds",
                'type'  => AmqpTopic::TYPE_DIRECT,
                'flags' => AmqpTopic::FLAG_DURABLE
            ],

            'queue' => [
                'name'  => "bet_processor.{$type}.pending.rounds",
                'flags' => AmqpQueue::FLAG_DURABLE
            ],

            'message' => [
                'deliveryMode' => AmqpMessage::DELIVERY_MODE_PERSISTENT,
                'routingKey'   => null
            ]
        ];
        self::manipulateLoop($data, $options);
    }

    public static function stopLoop($data, $table)
    {
        $options = [
            'topic' => [
                'name'  => "gameplay.{$table->game->slug}.table.{$table->id}",
                'type'  => AmqpTopic::TYPE_TOPIC,
                'flags' => AmqpTopic::FLAG_DURABLE
            ],

            'message' => [
                'routingKey'   => "*.{$table->game->slug}.table.{$table->id}",
                'deliveryMode' => AmqpMessage::DELIVERY_MODE_PERSISTENT
            ]
        ];

        if ( ! $data) {
            $data = ["command" => ["name" => "interrupt"]];
        }

        self::manipulateLoop($data, $options);
    }
    public static function restartLoop($data, $table)
    {
        $options = [
            'topic' => [
                'name'  => "gameplay.{$table->game->slug}.table.{$table->id}",
                'type'  => AmqpTopic::TYPE_TOPIC,
                'flags' => AmqpTopic::FLAG_DURABLE
            ],

            'message' => [
                'routingKey'   => "*.{$table->game->slug}.table.{$table->id}",
                'deliveryMode' => AmqpMessage::DELIVERY_MODE_PERSISTENT
            ]
        ];

        if ( ! $data) {
            $data = ["command" => ["name" => "resetInterrupted"]];
        }

        self::manipulateLoop($data, $options);
    }

    public static function restartLoopWithNoBets($data, $table)
    {
        $options = [
            'topic' => [
                'name'  => "gameplay.{$table->game->slug}.table.{$table->id}",
                'type'  => AmqpTopic::TYPE_TOPIC,
                'flags' => AmqpTopic::FLAG_DURABLE
            ],

            'message' => [
                'routingKey'   => "*.{$table->game->slug}.table.{$table->id}",
                'deliveryMode' => AmqpMessage::DELIVERY_MODE_PERSISTENT
            ]
        ];

        if ( ! $data) {
            $data = ["commands" => [["name" => "resetInterrupted"], ["name" => "skipBetAcception"]]];
        }

        self::manipulateLoop($data, $options);
    }

    protected static function manipulateLoop($data, $options)
    {
        $driver = new AmqpDriver();

        $context = $driver->createContext();
        $driver->publish($context, $data, $options);
        $context->close();
    }
}