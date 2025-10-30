<?php

namespace Dranzd\Common\EventSourcing\Domain\EventSourcing;

interface AggregateRoot
{
    public function reconstituteFromHistory(array $events): AggregateRoot;

    public function getRecordedEvents(): array;

    public function popRecordedEvents(): array;

    public function getAggregateRootUuid(): string;

    public function getAggregateRootType(): string;

    public function getAggregateRootVersion(): int;
}
