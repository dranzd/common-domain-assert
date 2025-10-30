<?php
namespace Dranzd\Common\EventSourcing;

abstract class EventSourcedAggregate
{
    protected array $uncommittedEvents = [];

    public function apply($event): void
    {
        $this->handleEvent($event);
        $this->uncommittedEvents[] = $event;
    }

    abstract protected function handleEvent($event): void;

    public function releaseEvents(): array
    {
        $events                  = $this->uncommittedEvents;
        $this->uncommittedEvents = [];
        return $events;
    }
}
