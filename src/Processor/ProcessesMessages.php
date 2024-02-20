<?php

namespace RamiiYoussef\Kafka\Processor;

interface ProcessesMessages
{
    /**
     * Process the given message
     *
     * @param \RamiiYoussef\Kafka\Processor\Message $message
     *
     * @return void
     */
    public function process(Message $message): void;
}
