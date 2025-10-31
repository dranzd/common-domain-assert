<?php

namespace Dranzd\Common\EventSourcing\Domain\Event;

use Dranzd\Common\EventSourcing\Domain\Message\GenericMessage;
use Dranzd\Common\EventSourcing\Domain\ValueObject\Identity\Uuid;

abstract class AbstractEvent extends GenericMessage implements Event
{
    public function __construct()
    {
        parent::__construct(
            Uuid::generateAsString(),
            static::expectedMessageName(),
            []
        );
    }

    final public function getEventName(): string
    {
        return $this->getMessageName();
    }

    /**
     * Returns when this event occurred
     * 
     * @return \DateTimeImmutable
     */
    public function occurredAt(): \DateTimeImmutable
    {
        return $this->getCreatedAt();
    }

    /**
     * Returns the event ID (UUID)
     * 
     * @return string
     */
    public function eventId(): string
    {
        return $this->getMessageUuid();
    }

    /**
     * Check if event has specific metadata key
     * 
     * @param string $key
     * @return bool
     */
    public function hasMetadata(string $key): bool
    {
        return $this->hasMetadataKey($key);
    }

    /**
     * Get metadata value by key or null if not exists
     * 
     * @param string $key
     * @return mixed|null
     */
    public function getMetadataValue(string $key)
    {
        return $this->getMetadataKeyValueOrDefault($key, null);
    }

    /**
     * Create a new instance with additional metadata
     * 
     * @param string $key
     * @param mixed $value
     * @return static
     */
    public function withAddedMetadata(string $key, $value): self
    {
        return $this->mergeMetadata([$key => $value]);
    }
}
