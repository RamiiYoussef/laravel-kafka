<?php

namespace RamiiYoussef\Kafka;

use Psr\Log\LoggerInterface;
use GrahamCampbell\Manager\AbstractManager;
use Illuminate\Contracts\Config\Repository;
use RamiiYoussef\Kafka\Contracts\Factory;
use RamiiYoussef\Kafka\Processor\BindsProcessors;
use RamiiYoussef\Kafka\Processor\ProcessesMessages;

class KafkaManager extends AbstractManager implements Factory, BindsProcessors
{
    /**
     * The factory instance.
     *
     * @var \RamiiYoussef\Kafka\KafkaFactory
     */
    protected $factory;

    /**
     * The processor instance.
     *
     * @var \RamiiYoussef\Kafka\Processor
     */
    protected $processor;

    /**
     * The logger instance.
     *
     * @var \Psr\Log\LoggerInterface
     */
    protected $log;

    /**
     * Create a new kafka manager instance.
     *
     * @param \Illuminate\Contracts\Config\Repository  $config
     * @param \RamiiYoussef\Kafka\KafkaFactory  $factory
     * @param \RamiiYoussef\Kafka\Processor  $processor
     * @param \Psr\Log\LoggerInterface  $log
     */
    public function __construct(
        Repository $config,
        KafkaFactory $factory,
        Processor $processor,
        LoggerInterface $log
    ) {
        $this->config = $config;
        $this->factory = $factory;
        $this->processor = $processor;
        $this->log = $log;
    }

    /**
     * Create the connection instance.
     *
     * @param array $config
     *
     * @return \RamiiYoussef\Kafka\Contracts\Kafka
     */
    protected function createConnection(array $config)
    {
        return new Kafka(
            $config,
            $this->factory,
            $this->factory->makeProducer($config),
            $this->processor,
            $this->log
        );
    }

    /**
     * Get the configuration name.
     *
     * @return string
     */
    protected function getConfigName()
    {
        return 'kafka';
    }

    /**
     * Get the configuration for a connection.
     *
     * @param string|null $name
     *
     * @throws \InvalidArgumentException
     *
     * @return array
     */
    public function getConnectionConfig(string $name = null)
    {
        $name = $name ?: $this->getDefaultConnection();

        return $this->getNamedConfig('connections', 'Connection', $name);
    }

    /**
     * Get the factory instance.
     *
     * @return \RamiiYoussef\Kafka\KafkaFactory
     */
    public function getFactory()
    {
        return $this->factory;
    }

    /**
     * @inheritDoc
     *
     * @throws \InvalidArgumentException
     */
    public function bind(string $topic, $processor, bool $force = false): ProcessesMessages
    {
        return $this->processor->bind($topic, $processor, $force);
    }
}
