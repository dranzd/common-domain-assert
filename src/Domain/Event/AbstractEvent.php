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
     * Get metadata value or null if not exists
     * 
     * @param string $key
     * @return mixed|null
     */
    public function metadata(string $key)
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

    /**
     * Create a new instance with causation ID metadata
     * 
     * @param string $causationId
     * @return static
     */
    public function withCausationId(string $causationId): self
    {
        return $this->withAddedMetadata('causation_id', $causationId);
    }

    /**
     * Create a new instance with correlation ID metadata
     * 
     * @param string $correlationId
     * @return static
     */
    public function withCorrelationId(string $correlationId): self
    {
        return $this->withAddedMetadata('correlation_id', $correlationId);
    }

    /**
     * Get causation ID if present
     * 
     * @return string|null
     */
    public function causationId(): ?string
    {
        return $this->metadata('causation_id');
    }

    /**
     * Get correlation ID if present
     * 
     * @return string|null
     */
    public function correlationId(): ?string
    {
        return $this->metadata('correlation_id');
    }
}
