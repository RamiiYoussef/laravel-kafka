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
        $this->publishes([
            __DIR__ . '/../config/kafka.php' => config_path('kafka.php'),
        ], 'laravel-kafka-config');
        
        $this->app['queue']->addConnector('kafka', function () {
            return new KafkaConnector();
        });
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->registerConfig();
    }

    
    /**
     * Setup the config.
     *
     * @return void
     */
    public function registerConfig()
    {
        $this->mergeConfigFrom(
            __DIR__ . '/../config/queue.php',
            'queue.connections.kafka'
        );

        $this->mergeConfigFrom(
            __DIR__ . '/../config/kafka.php',
            'kafka'
        );
    }
}
