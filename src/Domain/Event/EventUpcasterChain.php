<?php

namespace Dranzd\Common\EventSourcing\Domain\Event;

/**
 * Chain of event upcasters
 * 
 * Applies multiple upcasters in sequence to migrate events
 * through multiple versions (e.g., V1 -> V2 -> V3)
 * 
 * Usage:
 * 
 * $chain = new EventUpcasterChain([
 *     new AttachmentAddedV1ToV2Upcaster(),
 *     new AttachmentAddedV2ToV3Upcaster(),
 * ]);
 * 
 * $upcastedEvent = $chain->upcast($oldEventData);
 */
class EventUpcasterChain
{
    /**
     * @var EventUpcaster[]
     */
    private $upcasters = [];

    /**
     * @param EventUpcaster[] $upcasters
     */
    public function __construct(array $upcasters = [])
    {
        foreach ($upcasters as $upcaster) {
            $this->addUpcaster($upcaster);
        }
    }

    /**
     * Add an upcaster to the chain
     *
     * @param EventUpcaster $upcaster
     * @return self
     */
    public function addUpcaster(EventUpcaster $upcaster): self
    {
        $this->upcasters[] = $upcaster;
        return $this;
    }

    /**
     * Upcast event data through the chain
     * 
     * Applies all matching upcasters in sequence until no more apply.
     *
     * @param array $eventData
     * @return array
     */
    public function upcast(array $eventData): array
    {
        $eventName = $eventData['message_name'] ?? '';
        $currentVersion = $eventData['metadata']['_aggregate_event_version'] ?? 1;

        $maxIterations = 100; // Prevent infinite loops
        $iterations = 0;

        while ($iterations < $maxIterations) {
            $upcaster = $this->findUpcaster($eventName, $currentVersion);

            if ($upcaster === null) {
                // No more upcasters available
                break;
            }

            $eventData = $upcaster->upcast($eventData);
            $currentVersion = $upcaster->toVersion();
            $iterations++;
        }

        return $eventData;
    }

    /**
     * Check if event needs upcasting
     *
     * @param string $eventName
     * @param int $version
     * @return bool
     */
    public function needsUpcasting(string $eventName, int $version): bool
    {
        return $this->findUpcaster($eventName, $version) !== null;
    }

    /**
     * Find an upcaster for the given event and version
     *
     * @param string $eventName
     * @param int $version
     * @return EventUpcaster|null
     */
    private function findUpcaster(string $eventName, int $version): ?EventUpcaster
    {
        foreach ($this->upcasters as $upcaster) {
            if ($upcaster->canUpcast($eventName, $version)) {
                return $upcaster;
            }
        }

        return null;
    }

    /**
     * Get all registered upcasters
     *
     * @return EventUpcaster[]
     */
    public function getUpcasters(): array
    {
        return $this->upcasters;
    }
}
