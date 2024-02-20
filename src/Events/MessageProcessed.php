<?php

namespace RamiiYoussef\Kafka\Events;

use RamiiYoussef\Kafka\Processor\Message;

class MessageProcessed
{
    /**
     * The connection name.
     *
     * @var string
     */
    public $connectionName;

    /**
     * The message instance.
     *
     * @var \RamiiYoussef\Kafka\Processor\Message
     */
    public $message;

    /**
     * Create a new message instance.
     *
     * @param  string  $connectionName
     * @param  \RamiiYoussef\Kafka\Processor\Message  $message
     */
    public function __construct(string $connectionName, Message $message)
    {
        $this->message = $message;
        $this->connectionName = $connectionName;
    }
}
