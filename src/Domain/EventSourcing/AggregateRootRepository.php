<?php

namespace Dranzd\Common\EventSourcing\Domain\EventSourcing;

interface AggregateRootRepository
{
    /**
     * @return AggregateEvent[]
     */
    public function store(AggregateRoot $aggregateRoot): array;
}
