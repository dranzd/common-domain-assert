<?php

namespace Dranzd\Common\EventSourcing\Domain\Message;

trait CausationCapableTrait
{
    final public function setCausationId(string $id = null): void
    {
        $this->mergeMetadata(
            [
                CausationCapable::META_CAUSATION_ID => $id,
            ]
        );
    }

    final public function setCausationName(string $name = null): void
    {
        $this->mergeMetadata(
            [
                CausationCapable::META_CAUSATION_NAME => $name,
            ]
        );
    }

    /**
     * @return static
     */
    final public function withCausationId(string $id = null): Message
    {
        return $this->withMetadata(
            \array_merge(
                $this->getMetadata(),
                [
                    CausationCapable::META_CAUSATION_ID => $id,
                ]
            )
        );
    }

    /**
     * @return static
     */
    final public function withCausationName(string $name = null): Message
    {
        return $this->withMetadata(
            \array_merge(
                $this->getMetadata(),
                [
                    CausationCapable::META_CAUSATION_NAME => $name,
                ]
            )
        );
    }

    final public function getCausationId(): string
    {
        return $this->getMetadataKeyValueOrDefault(CausationCapable::META_CAUSATION_ID, '');
    }

    final public function getCausationName(): string
    {
        return $this->getMetadataKeyValueOrDefault(CausationCapable::META_CAUSATION_NAME, '');
    }
}
