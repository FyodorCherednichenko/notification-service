<?php

namespace App\Queue\Connectors;

use App\ValueObjects\RabbitMQQueueConfig;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

class RabbitMQHandler
{
    protected $connection;
    protected RabbitMQQueueConfig $config;
    protected $declaredQueues = [];
    
    public function __construct(?RabbitMQQueueConfig $config = null)
    {
        $this->config = $config ?? new RabbitMQQueueConfig();

        $this->connection = new AMQPStreamConnection(
            host: config('rabbitmq.host'),
            port: config('rabbitmq.port'),
            user: config('rabbitmq.user'),
            password: config('rabbitmq.password'),
            vhost: config('rabbitmq.vhost')
        );
    }

    public function publish(string $queue, string $message, int $priority = 0): bool
    {
        $this->declareQueue($queue);

        $channel = $this->connection->channel();
        
        $msg = new AMQPMessage($message, [
            'delivery_mode' => AMQPMessage::DELIVERY_MODE_PERSISTENT,
            'priority' => min($priority, $this->config->maxPriority)
        ]);
        
        $channel->basic_publish($msg, '', $queue);
        $channel->close();
        
        return true;
    }

    public function consume(string $queue, callable $callback, int $maxMessages = PHP_INT_MAX): void
    {
        $this->declareQueue($queue);
        
        $channel = $this->connection->channel();

        $messagesProcessed = 0;
        
        $channel->basic_consume($queue, '', false, false, false, false, function($msg) use ($callback, &$messagesProcessed, $maxMessages) {
            try {
                $callback($msg->body);
                $msg->ack();
                $messagesProcessed++;
            } catch (\Throwable $e) {
                $msg->nack(false, true);
            }
        });
        
        while ($channel->is_open() && $messagesProcessed < $maxMessages) {
            $channel->wait();
        }
        
        $channel->close();
    }

    protected function declareQueue(string $queue): void
    {
        if (isset($this->declaredQueues[$queue])) {
            return;
        }

        $channel = $this->connection->channel();
        
        $args = [];
        if ($this->config->maxPriority > 0) {
            $args['x-max-priority'] = ['I', $this->config->maxPriority];
        }
        
        $channel->queue_declare(
            $queue,
            false,
            $this->config->durable,
            $this->config->exclusive,
            $this->config->autoDelete,
            false,
            $args
        );
        
        $channel->close();
        $this->declaredQueues[$queue] = true;
    }

    public function ping()
    {
        return $this->connection->isConnected();
    }
    
    public function close()
    {
        $this->connection->close();
    }
}