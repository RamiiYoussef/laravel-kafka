<?php

namespace RamiiYoussef\Kafka;

use Illuminate\Support\ServiceProvider;
use RamiiYoussef\Kafka\Queue\Connectors\KafkaConnector;

class KafkaServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->app['queue']->addConnector('kafka', function () {
            return new KafkaConnector();
        });
    }
}
