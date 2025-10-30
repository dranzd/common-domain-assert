<?php

namespace Dranzd\Common\EventSourcing\Domain\Event;

interface VersionedEvent extends Event
{
    /**
     * Returns the version of this specific event instance
     * 
     * This is the version that was stored when the event was persisted.
     * Used to determine if event needs upcasting when reconstituting.
     *
     * @return int
     */
    public function getEventVersion(): int;

    /**
     * Returns the latest/current version of this event type
     * 
     * This represents the current schema version in the codebase.
     * When this differs from getEventVersion(), upcasting may be needed.
     *
     * @return int
     */
    public static function getLatestVersion(): int;
}
