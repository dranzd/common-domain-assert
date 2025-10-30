<?php

namespace Dranzd\Common\EventSourcing\Domain\EventSourcing;

interface AggregateVisitor
{
    public function visit(AggregateRoot $aggregateRoot): void;
}
