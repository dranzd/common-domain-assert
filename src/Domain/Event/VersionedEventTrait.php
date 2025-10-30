<?php

namespace Dranzd\Common\EventSourcing\Domain\Event;

use Dranzd\Common\EventSourcing\Domain\EventSourcing\AggregateEvent;

/**
 * Trait to help implement VersionedEvent interface
 * 
 * Usage:
 * 
 * final class MyEvent extends AbstractAggregateEvent implements VersionedEvent
 * {
 *     use VersionedEventTrait;
 *     
 *     const CURRENT_VERSION = 2;
 *     
 *     // Optionally override getEventVersion() if you need custom logic
 *     // Optionally add upcaster methods like upcastFromV1()
 * }
 */
trait VersionedEventTrait
{
    /**
     * Returns the version of this specific event instance
     * 
     * Reads from metadata if available, otherwise returns current version.
     * Override this method if you need custom version detection logic.
     *
     * @return int
     */
    public function getEventVersion(): int
    {
        if (!method_exists($this, 'getMetadataKeyValueOrDefault')) {
            return static::getLatestVersion();
        }

        return $this->getMetadataKeyValueOrDefault(
            AggregateEvent::META_AGGREGATE_EVENT_VERSION,
            static::getLatestVersion()
        );
    }

    /**
     * Returns the latest/current version of this event type
     * 
     * Reads from CURRENT_VERSION constant if defined, otherwise returns 1.
     * Override this method or define CURRENT_VERSION constant in your event class.
     *
     * @return int
     */
    public static function getLatestVersion(): int
    {
        if (\defined(static::class . '::CURRENT_VERSION')) {
            return \constant(static::class . '::CURRENT_VERSION');
        }

        return 1;
    }

    /**
     * Check if this event needs upcasting
     *
     * @return bool
     */
    public function needsUpcasting(): bool
    {
        return $this->getEventVersion() < static::getLatestVersion();
    }

    /**
     * Get the version difference (how many versions behind)
     *
     * @return int
     */
    public function versionDifference(): int
    {
        return static::getLatestVersion() - $this->getEventVersion();
    }
}
