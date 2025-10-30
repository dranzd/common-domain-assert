<?php

namespace Dranzd\Common\EventSourcing\Domain\EventSourcing;

use Dranzd\Common\EventSourcing\Domain\Event\Event;

interface AggregateEvent extends Event
{
    public const META_AGGREGATE_ROOT_UUID     = '_aggregate_root_uuid';
    public const META_AGGREGATE_ROOT_TYPE     = '_aggregate_root_type';
    public const META_AGGREGATE_ROOT_VERSION  = '_aggregate_root_version';
    public const META_AGGREGATE_EVENT_VERSION = '_aggregate_event_version';

    public function getAggregateRootUuid(): string;

    public function getAggregateRootType(): string;

    public function getAggregateRootVersion(): int;

    public function getAggregateEventVersion(): int;
}
