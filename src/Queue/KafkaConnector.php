<?php

namespace RamiiYoussef\Kafka\Queue;

use RamiiYoussef\Kafka\KafkaManager;
use Illuminate\Support\Arr;
use Illuminate\Queue\Connectors\ConnectorInterface;
use RdKafka\Conf;
use RdKafka\Consumer;
use RdKafka\Producer;
use RdKafka\TopicConf;
use Psr\Log\LoggerInterface;
use RamiiYoussef\Kafka\Queue\KafkaQueue;

class KafkaConnector implements ConnectorInterface
{
    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $log;

    /**
     * @var \RamiiYoussef\Kafka\KafkaManager
     */
    private $manager;

    /**
     * KafkaConnector constructor.
     *
     * @param \RamiiYoussef\Kafka\KafkaManager  $manager
     * @param \Psr\Log\LoggerInterface  $log
     */
    public function __construct(KafkaManager $manager, LoggerInterface $log)
    {
        $this->manager = $manager;
        $this->log = $log;
    }

    /**
     * Establish a queue connection.
     *
     * @param array $config
     *
     * @return \Illuminate\Contracts\Queue\Queue
     */
    public function connect(array $config)
    {
        $kafka = $this->manager->connection($config['connection'] ?? 'default');

        return new KafkaQueue(
            $kafka,
            $config,
            $this->log
        );
    }
}
