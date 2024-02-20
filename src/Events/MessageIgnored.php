<?php

namespace RamiiYoussef\Kafka\Events;

use Throwable;
use RamiiYoussef\Kafka\Processor\Message;

class MessageIgnored
{
    /**
     * The connection name.
     *
     * @var string
     */
    public $connectionName;

    /**
     * The job instance.
     *
     * @var \RamiiYoussef\Kafka\Processor\Message
     */
    public $message;

    /**
     * The exception instance.
     *
     * @var \Throwable
     */
    public $exception;

    /**
     * Create a new event instance.
     *
     * @param  string  $connectionName
     * @param  \RamiiYoussef\Kafka\Processor\Message  $message
     * @param  \Throwable  $exception
     */
    public function __construct(string $connectionName, Message $message, Throwable $exception)
    {
        $this->message = $message;
        $this->exception = $exception;
        $this->connectionName = $connectionName;
    }
}
