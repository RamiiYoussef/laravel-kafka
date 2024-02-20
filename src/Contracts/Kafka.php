<?php

namespace RamiiYoussef\Kafka\Contracts;

use RamiiYoussef\Kafka\Message;
use RamiiYoussef\Kafka\Consumer;

interface Kafka
{
    public function produce(Message $message): void;

    public function consume($topic = null, $processor = null): void;

    public function consumer($topic = null, bool $checkUnboundTopics = false): Consumer;

    public function getConfig(): array;
}
