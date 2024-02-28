<?php

namespace RamiiYoussef\Kafka\Queue;

use Illuminate\Contracts\Queue\Queue as QueueInterface;
use Illuminate\Queue\Queue;
use Illuminate\Support\Facades\Log;
use RamiiYoussef\Kafka\Consumer;
use RamiiYoussef\Kafka\Producer;
use RamiiYoussef\Kafka\Queue\Jobs\KafkaJob;
use RuntimeException;
use Throwable;

class KafkaQueue extends Queue implements QueueInterface
{
    /** @var Producer */
    private Producer $producer;

    /** @var Consumer */
    private Consumer $consumer;

    /** @var string */
    private string $defaultQueue;

    /**
     * @param Producer $producer
     * @param Consumer $consumer
     * @param string $defaultQueue
     */
    public function __construct(Producer $producer, Consumer $consumer, string $defaultQueue)
    {
        $this->producer = $producer;
        $this->consumer = $consumer;
        $this->defaultQueue = $defaultQueue;
    }

    /**
     * @param string|null $queue
     * @return int
     */
    public function size($queue = null): int
    {
        Log::warning('Kafka queue does not support retrieving size');

        return 0;
    }

    /**
     * @param string|object $job
     * @param mixed $data
     * @param string|null $queue
     * @return mixed|null
     * @throws Throwable
     */
    public function push($job, $data = '', $queue = null)
    {
        $topic = $this->getTopic($queue);

        return $this->pushRaw(
            $this->createPayload($job, $topic, $data),
            $topic
        );
    }

    /**
     * @param string $payload
     * @param string|null $queue
     * @param array $options
     * @return mixed
     * @throws Throwable
     */
    public function pushRaw($payload, $queue = null, array $options = [])
    {
        $this->producer->produce(
            $queue,
            $payload,
            data_get($options, 'available_at')
        );

        return data_get(json_decode($payload), 'uuid') ?? null;
    }

    /**
     * @param mixed $delay
     * @param mixed $job
     * @param mixed $data
     * @param mixed $queue
     * @return mixed
     * @throws Throwable
     */
    public function later($delay, $job, $data = '', $queue = null)
    {
        $topic = $this->getTopic($queue, true);

        return $this->pushRaw(
            $this->createPayload($job, $topic, $data),
            $topic,
            ['available_at' => (string)$this->availableAt($delay)]
        );
    }

    /**
     * @param int $delay
     * @param KafkaJob $kafkaJob
     */
    public function release($delay, $kafkaJob)
    {
        $body = $kafkaJob->payload();
        /*
         * Some jobs don't have the command set, so fall back to just sending it the job name string
         */
        if (isset($body['data']['command']) === true) {
            $job = unserialize($body['data']['command']);
        } else {
            $job = $kafkaJob->getName();
        }
        $data = $body['data'];

        $topic = $this->getTopic($kafkaJob->getQueue(), true);
        $payload = $this->createPayload($job, $topic, $data);
        $payloadAr = json_decode($payload, true);
        $payloadAr['attempts'] = $kafkaJob->attempts();
        $payload = json_encode($payloadAr, \JSON_UNESCAPED_UNICODE);
        $options = $delay ? ['available_at' => (string)$this->availableAt($delay)] : [];
        return $this->pushRaw($payload, $topic, $options);
    }

    /**
     * @param string|null $queue
     * @return KafkaJob|void
     * @throws RuntimeException
     * @throws Throwable
     */
    public function pop($queue = null)
    {
        return $this->popNextJob($queue);
    }

    /**
     * @param string|null $queue
     * @param string|null $firstRequeuedJobId
     * @return KafkaJob|null
     * @throws Throwable
     */
    private function popNextJob(string $queue = null, string $firstRequeuedJobId = null): ?KafkaJob
    {
        $message = $this->consumer->consume($this->getTopic($queue));

        if ($message === null) {
            return null;
        }

        $job = new KafkaJob(
            $this->container,
            $this,
            $message->payload,
            $message->topic_name,
            $message,
            $this->connectionName
        );

        return $this->ensureJobCanBeProcessed($job, $queue, $firstRequeuedJobId);
    }

    /**
     * @param KafkaJob $job
     * @param string|null $queue
     * @param string|null $firstRequeuedJobId
     * @return KafkaJob|null
     * @throws Throwable
     */
    private function ensureJobCanBeProcessed(
        KafkaJob $job,
        ?string $queue,
        ?string $firstRequeuedJobId
    ): ?KafkaJob {
        if ($job->getMessageTimestamp() > now()->timestamp) {
            $this->requeueJob($queue, $job);

            if ($job->uuid() === $firstRequeuedJobId) {
                return null;
            }

            return $this->popNextJob($queue, $firstRequeuedJobId ?? $job->uuid());
        }

        $this->consumer->commitOffset();

        return $job;
    }

    /**
     * @param string|null $queue
     * @param KafkaJob $job
     * @return void
     * @throws Throwable
     */
    private function requeueJob(?string $queue, KafkaJob $job): void
    {
        $this->producer->produce(
            $this->getTopic($queue, true),
            $job->getRawBody(),
            $job->getMessageTimestamp(),
            fn() => $this->consumer->commitOffset()
        );
    }

    /**
     * @param string|null $queue
     * @param bool $isDelayed
     * @return string
     */
    private function getTopic(?string $queue, bool $isDelayed = false): string
    {
        return $queue ?? $this->defaultQueue;
    }
}
