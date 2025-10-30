<?php

namespace Dranzd\Common\EventSourcing\Domain\EventSourcing;

interface AggregateVisitable
{
    public function acceptVisitor(AggregateVisitor $aggregateVisitor): void;
}
