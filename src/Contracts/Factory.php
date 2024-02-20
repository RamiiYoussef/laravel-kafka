<?php

namespace RamiiYoussef\Kafka\Contracts;

interface Factory
{
    /**
     * Resolve a kafka connection instance.
     *
     * @param  string|null  $name
     * @return \RamiiYoussef\Kafka\Contracts\Kafka
     */
    public function connection(?string $name = null);
}
