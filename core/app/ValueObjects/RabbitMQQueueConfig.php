<?php

namespace App\ValueObjects;

class RabbitMQQueueConfig
{
    public bool $durable = true;
    public bool $exclusive = false;
    public bool $autoDelete = false;
    public int $maxPriority = 10;
}