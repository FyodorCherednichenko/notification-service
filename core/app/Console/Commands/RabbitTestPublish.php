<?php

namespace App\Console\Commands;

use App\Queue\Connectors\RabbitMQHandler;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('app:rabbit-test-publish')]
#[Description('Command description')]
class RabbitTestPublish extends Command
{
    /**
     * Execute the console command.
     */
    public function handle()
    {
        $rabbit = new RabbitMQHandler();
        $rabbit->publish('test_queue', '{"test":1}', 5);
    }
}
