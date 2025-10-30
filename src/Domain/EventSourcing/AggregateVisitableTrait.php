<?php

namespace Dranzd\Common\EventSourcing\Domain\EventSourcing;

trait AggregateVisitableTrait
{
    final public function acceptVisitor(AggregateVisitor $aggregateVisitor): void
    {
        $aggregateVisitor->visit($this);
    }
}
