<?php

namespace RamiiYoussef\Kafka\Processor;

interface BindsProcessors
{
    /**
     * Bind a processor to a specific topic
     *
     * @param  string $topic
     * @param  string|callable|ProcessesMessages $processor
     * @param  boolean $force
     *
     * @throws \RamiiYoussef\Kafka\Exceptions\TopicAlreadyBoundException
     *
     * @return \RamiiYoussef\Kafka\Processor\ProcessesMessages
     */
    public function bind(string $topic, $processor, bool $force = false): ProcessesMessages;
}
