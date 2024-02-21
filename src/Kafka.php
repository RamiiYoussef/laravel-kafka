<?php

namespace RamiiYoussef\Kafka;

use Psr\Log\LoggerInterface;
use RamiiYoussef\Kafka\Processor\ProcessesMessages;
use RamiiYoussef\Kafka\Contracts\Kafka as KafkaContract;
use RamiiYoussef\Kafka\Exceptions\TopicNotBoundException;
use RamiiYoussef\Kafka\Processor\Message as ProcessorMessage;

class Kafka implements KafkaContract
{
    /**
     * @var array
     */
    private $config;

    /**
     * @var \RamiiYoussef\Kafka\Producer
     */
    private $producer;

    /**
     * @var \RamiiYoussef\Kafka\KafkaFactory
     */
    private $factory;

    /**
     * @var \RamiiYoussef\Kafka\Processor
     */
    private $processor;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $log;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    private array $subscribedTopics;

    /**
     * Kafka constructor
     *
     * @param array $config
     * @param \RamiiYoussef\Kafka\KafkaFactory $factory
     * @param \RamiiYoussef\Kafka\Producer $producer
     * @param \RamiiYoussef\Kafka\Processor $processor
     * @param \Psr\Log\LoggerInterface $log
     */
    public function __construct(
        array $config,
        KafkaFactory $factory,
        Producer $producer,
        Processor $processor,
        LoggerInterface $log
    ) {
        $this->producer = $producer;
        $this->factory = $factory;
        $this->processor = $processor;
        $this->config = $config;
        $this->log = $log;
        $this->subscribedTopics = [];
    }

    /**
     * Produce a message
     *
     * @param  \RamiiYoussef\Kafka\Message $message
     * @return void
     */
    public function produce(Message $message): void
    {
        $this->log->debug("[Kafka] Producing message on topic '{$message->getTopic()}'.", [
            'message' => $message
        ]);

        $this->producer->produce($message);
    }

    /**
     * Start a long-running consumer
     *
     * @param  string|array  $topic
     * @param  string|callable|ProcessesMessages|null  $processor
     * @return void
     */
    public function consume($topic = null, $processor = null): void
    {
        if ($processor) {
            $this->processor->bind($topic, $processor);
        }

        $consumer = $this->subscribe($topic, true);

        // Start the long running consumer
        while (true) {
            // Receive a message from the consumer. If it was
            // null, re-iterate.
            if (null === ($message = $consumer->receive())) {
                continue;
            }

            // Forward the message to the processor
            $this->process($message, $consumer);
        }
    }

    /**
     * Create a consumer instance for the given topic. If
     * $checkUnboundTopics is set to true, it is ensured
     * that topics have a message processor bound to it.
     *
     * @param  string|array|null $topic
     * @param  boolean  $checkUnboundTopics
     *
     * @throws \RamiiYoussef\Kafka\Exceptions\TopicNotBoundException
     *
     * @return \RamiiYoussef\Kafka\Consumer
     */
    public function consumer($topic = null, bool $checkUnboundTopics = false): Consumer
    {
        return $this->subscribe($topic, $checkUnboundTopics);
    }

    /**
     * Returns the connection configuration
     *
     * @return array
     */
    public function getConfig(): array
    {
        return $this->config;
    }

    /**
     * Create a consumer and bind it to the given topic(s). If no
     * topics are given, the connections' default topics are used.
     * If $checkUnboundTopics is set to true, it is ensured that
     * given topics have a message processor bound to it.
     *
     * @param  string|array|null $topic
     * @param  boolean  $checkUnboundTopics
     *
     * @throws \RamiiYoussef\Kafka\Exceptions\TopicNotBoundException
     *
     * @return \RamiiYoussef\Kafka\Consumer
     */
    private function subscribe($topic, bool $checkUnboundTopics = false): Consumer
    {
        $topics = $this->getTopics($topic);

        if ($checkUnboundTopics && $this->shouldFailOnUnboundTopics()) {
            $this->enforceProcessorsForTopics($topics);
        }

        $consumer = $this->factory->makeConsumer($topics, $this->config);
        $consumer->subscribe();

        return $consumer;
    }

    /**
     * Forwards the message received on the given consumer
     * to either the given $processor, or to the default
     * event processor.
     *
     * @param  \RamiiYoussef\Kafka\Message $message
     * @param  \RamiiYoussef\Kafka\Consumer $consumer
     * @return void
     */
    private function process(Message $message, Consumer $consumer): void
    {
        $processMessage = ProcessorMessage::wrap($message);

        $this->processor->process($processMessage);

        if ($processMessage->isAcknowledged()) {
            $consumer->commit($message);
        } elseif ($processMessage->isRejected()) {
            $consumer->reject($message);
        } elseif ($processMessage->isRequeued()) {
            $consumer->reject($message, true);
        }
    }

    /**
     * Get topics as array
     *
     * @param  array|string|null  $topic
     * @return string[]
     */
    private function getTopics($topic)
    {
        if (is_null($topic)) {
            return [$this->config['topic']];
        }
        return is_array($topic) ? $topic : [$topic];
    }

    /**
     * Ensures that a processor is available for
     * every given topic by throwing an exception
     * if this is not the case.
     *
     * @param  string[] $topics
     *
     * @throws \RamiiYoussef\Kafka\Exceptions\TopicNotBoundException
     *
     * @return void
     */
    private function enforceProcessorsForTopics(array $topics): void
    {
        foreach ($topics as $topic) {
            if (!$this->processor->has($topic)) {
                throw new TopicNotBoundException($topic);
            }
        }
    }

    /**
     * Returns true if the consumer should fail on
     * unbound topics, false otherwise.
     *
     * @return boolean
     */
    private function shouldFailOnUnboundTopics()
    {
        return $this->config['unhandled_action'] === 'fail';
    }
}
