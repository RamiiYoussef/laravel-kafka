# laravel-kafka: A queue driver for Laravel

laravel-kafka adds support for Apache Kafka to Laravel Queues. It builds upon the [rdkafka](https://github.com/arnaud-lb/php-rdkafka) php extension, which you will have to install seperately. Also, you have to install the C/C++ client library [librdkafka](https://github.com/edenhill/librdkafka) upfront. Afterwards, you can freely push jobs to your favorite Kafka queue!

laravel-kafka supports PHP 8.1/8.2 and Laravel 9/10.

## Installation

To install the latest version of `ramiiyoussef/laravel-kafka` just require it using composer.

```bash
composer require ramiiyoussef/laravel-kafka
```

This package is using Laravel's package auto-discovery, so it doesn't require you to manually add the ServiceProvider. If you've opted out of this feature, add the ServiceProvider to the providers array in config/app.php:

```php
RamiiYoussef\Kafka\KafkaServiceProvider::class,
```

```bash
php artisan vendor:publish --provider="RamiiYoussef\Kafka\KafkaServiceProvider"
```

## Licence

Kafkaesk is licenced under The MIT Licence (MIT).
