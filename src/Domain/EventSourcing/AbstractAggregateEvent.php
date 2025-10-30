<?php

namespace Dranzd\Common\EventSourcing\Domain\EventSourcing;

use Dranzd\Common\EventSourcing\Domain\Event\AbstractEvent;
use Dranzd\Common\EventSourcing\Domain\EventSourcing\AggregateEvent;
use Dranzd\Common\EventSourcing\Domain\Message\ActorCapable;
use Dranzd\Common\EventSourcing\Domain\Message\ActorCapableTrait;
use Dranzd\Common\EventSourcing\Domain\Message\CausationCapable;
use Dranzd\Common\EventSourcing\Domain\Message\CausationCapableTrait;
use Dranzd\Common\EventSourcing\Domain\Message\CorrelationCapable;
use Dranzd\Common\EventSourcing\Domain\Message\CorrelationCapableTrait;

abstract class AbstractAggregateEvent extends AbstractEvent implements
    AggregateEvent,
    CausationCapable,
    CorrelationCapable,
    ActorCapable
{
    use CausationCapableTrait, CorrelationCapableTrait, ActorCapableTrait;

    final public function getAggregateRootUuid(): string
    {
        return $this->getMetadataKeyValueOrDefault(AggregateEvent::META_AGGREGATE_ROOT_UUID, '');
    }

    final public function getAggregateRootType(): string
    {
        return $this->getMetadataKeyValueOrDefault(AggregateEvent::META_AGGREGATE_ROOT_TYPE, '');
    }

    final public function getAggregateRootVersion(): int
    {
        return $this->getMetadataKeyValueOrDefault(AggregateEvent::META_AGGREGATE_ROOT_VERSION, 0);
    }

    final public function getAggregateEventVersion(): int
    {
        return $this->getMetadataKeyValueOrDefault(AggregateEvent::META_AGGREGATE_EVENT_VERSION, 0);
    }
}
