<?php

declare(strict_types=1);

namespace App\Client\AMQP;

class AMQPClient
{
    private \AMQPConnection $amqpConnection;

    public function __construct(string $dsn)
    {
        if (false === $credentials = parse_url($dsn)) {
            throw new \Exception(sprintf('The given AMQP DSN "%s" is invalid.', $dsn));
        }

        $this->amqpConnection = new \AMQPConnection([
            'host'     => $credentials['host'] ?? null,
            'port'     => $credentials['port'] ?? null,
            'login'    => $credentials['user'] ?? null,
            'password' => $credentials['pass'] ?? null,
        ]);
    }

    public function getQueueMessages(string $queue): int
    {
        $this->amqpConnection->connect();

        $amqpChannel = new \AMQPChannel($this->amqpConnection);
        $amqpQueue   = new \AMQPQueue($amqpChannel);
        $amqpQueue->setFlags(AMQP_PASSIVE);
        $amqpQueue->setName($queue);

        $messages = $amqpQueue->declareQueue();

        $this->amqpConnection->disconnect();

        return $messages;
    }
}
