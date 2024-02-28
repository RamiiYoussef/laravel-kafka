# laravel-kafka: A queue driver for Laravel

laravel-kafka adds support for Apache Kafka to Laravel Queues. It builds upon the [rdkafka](https://github.com/arnaud-lb/php-rdkafka) php extension, which you will have to install seperately. Also, you have to install the C/C++ client library [librdkafka](https://github.com/edenhill/librdkafka) upfront. Afterwards, you can freely push jobs to your favorite Kafka queue!

laravel-kafka supports PHP 8.1/8.2/8.3 and Laravel 10.

## Installation

To install the latest version of `ramiiyoussef/laravel-kafka` just require it using composer.

```bash
composer require ramiiyoussef/laravel-kafka
```

Environments:
```bash
KAFKA_BROKER_LIST=127.0.0.1:9092
KAFKA_QUEUE=topic_a
KAFKA_QUEUE_GROUP=group1
KAFKA_AUTH_LOGIN=
KAFKA_AUTH_PASSWORD=
```

## Licence

Kafkaesk is licenced under The MIT Licence (MIT).
