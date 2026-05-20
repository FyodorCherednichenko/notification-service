<?php

namespace App\Console\Commands;

use App\Queue\Connectors\RabbitMQHandler;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('app:rabbit-test-consume')]
#[Description('Command description')]
class RabbitTestConsume extends Command
{
    /**
     * Execute the console command.
     */
    public function handle()
    {
        $rabbit = new RabbitMQHandler();
        $rabbit->consume('test_queue', function($msg) {
            echo "📨 Получено: " . $msg . "\n"; 
        });
    }
}
