<?php

namespace Dranzd\Common\EventSourcing\Domain\EventSourcing;

trait AggregateRootTrait
{
    /**
     * @var int
     */
    private $aggregateVersion = 0;

    /**
     * @var array
     */
    private $aggregateRecordedEvents = [];

    /**
     * @var array
     */
    private $aggregateAppliedEvents = [];

    final public function reconstituteFromHistory(array $events): AggregateRoot
    {
        $obj = new static();

        $obj->replay($events);

        return $obj;
    }

    final public function getRecordedEvents(): array
    {
        return $this->aggregateRecordedEvents;
    }

    final public function popRecordedEvents(): array
    {
        $events = $this->aggregateRecordedEvents;

        $this->aggregateRecordedEvents = [];

        return $events;
    }

    final public function getAggregateRootType(): string
    {
        return \get_class($this);
    }

    final public function getAggregateRootVersion(): int
    {
        return $this->aggregateVersion;
    }

    private function recordThat(AggregateEvent $event): AggregateEvent
    {
        $this->aggregateVersion += 1;

        $event = $event->mergeMetadata(
            [
                /** @var AggregateRoot $this */
                AggregateEvent::META_AGGREGATE_ROOT_UUID    => $this->getAggregateRootUuid(),
                AggregateEvent::META_AGGREGATE_ROOT_TYPE    => $this->getAggregateRootType(),
                AggregateEvent::META_AGGREGATE_ROOT_VERSION => $this->getAggregateRootVersion(),
            ]
        );

        $this->aggregateRecordedEvents[] = $event;

        $this->apply($event);

        return $event;
    }

    private function replay($events): void
    {
        /** @var AggregateEvent $event */
        foreach ($events as $event) {
            $this->aggregateVersion = $event->getAggregateRootVersion();
            $this->apply($event);
        }
    }

    private function apply(AggregateEvent $event): void
    {
        $reflection = new \ReflectionClass($event);
        $name       = $reflection->getShortName();
        $method     = "applyOn{$name}";

        if (!\method_exists($this, $method)) {
            throw new \BadMethodCallException(
                \sprintf(
                    'Call to method %s::%s not found',
                    \get_class($this),
                    $method
                )
            );
        }

        \call_user_func([$this, $method], $event);

        $this->aggregateAppliedEvents[] = $event;
    }
}
