<?php

namespace Dranzd\Common\EventSourcing\Domain\Message;

trait CorrelationCapableTrait
{
    final public function setCorrelationId(string $id = null): void
    {
        $this->mergeMetadata(
            [
                CorrelationCapable::META_CORRELATION_ID => $id,
            ]
        );
    }

    /**
     * @return static
     */
    final public function withCorrelationId(string $id = null): Message
    {
        return $this->withMetadata(
            \array_merge(
                $this->getMetadata(),
                [
                    CorrelationCapable::META_CORRELATION_ID => $id,
                ]
            )
        );
    }

    final public function getCorrelationId(): string
    {
        return $this->getMetadataKeyValueOrDefault(CorrelationCapable::META_CORRELATION_ID, '');
    }
}
