<?php

namespace RamiiYoussef\Kafka;

use RamiiYoussef\Kafka\Queue\QueueConfig;
use RdKafka\KafkaConsumer;
use RdKafka\Message;
use RuntimeException;

class Consumer
{
    public const CONFIG_GROUP_ID = 'group.id';
    public const CONFIG_HEARTBEAT_INTERVAL_MS = 'heartbeat.interval.ms';
    public const CONFIG_AUTO_OFFSET_RESET = 'auto.offset.reset';

    /** @var KafkaConsumer */
    private KafkaConsumer $consumer;

    /** @var int */
    private int $timeout;

    /**
     * @param QueueConfig $queueConfig
     * @param string $groupName
     * @param int $timeout
     * @param int $heartbeat
     */
    public function __construct(QueueConfig $queueConfig, string $groupName, int $timeout, int $heartbeat)
    {
        $queueConfig->set(self::CONFIG_GROUP_ID, $groupName);
        $queueConfig->set(self::CONFIG_HEARTBEAT_INTERVAL_MS, "{$heartbeat}");
        $queueConfig->set(self::CONFIG_AUTO_OFFSET_RESET, 'earliest');
        $this->timeout = $timeout;
        $this->consumer = new KafkaConsumer($queueConfig);
    }

    /**
     * @param string $topic
     * @return Message|null
     * @throws RuntimeException
     */
    public function consume(string $topic): ?Message
    {
        if (!in_array($topic, $this->consumer->getSubscription())) {
            $this->consumer->subscribe([$topic]);
        }

        $message = $this->consumer->consume($this->timeout);

        switch ($message->err) {
            case RD_KAFKA_RESP_ERR_NO_ERROR:
                return $message;
            case RD_KAFKA_RESP_ERR__PARTITION_EOF:
            case RD_KAFKA_RESP_ERR__TIMED_OUT:
            case RD_KAFKA_RESP_ERR__UNKNOWN_PARTITION:
            case RD_KAFKA_RESP_ERR__UNKNOWN_TOPIC:
            case RD_KAFKA_RESP_ERR_UNKNOWN_TOPIC_OR_PART:
                return null;
            default:
                throw new RuntimeException($message->errstr(), $message->err);
        }
    }

    /**
     * @return void
     */
    public function commitOffset(): void
    {
        $this->consumer->commit();
    }
}
