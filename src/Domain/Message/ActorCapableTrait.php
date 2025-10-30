<?php

namespace Dranzd\Common\EventSourcing\Domain\Message;

trait ActorCapableTrait
{
    final public function setActorId(string $id = null): void
    {
        $this->mergeMetadata(
            [
                ActorCapable::META_ACTOR_ID => $id,
            ]
        );
    }

    final public function setActorName(string $name = null): void
    {
        $this->mergeMetadata(
            [
                ActorCapable::META_ACTOR_NAME => $name,
            ]
        );
    }

    /**
     * @return static
     */
    final public function withActorId(string $id = null): Message
    {
        return $this->withMetadata(
            \array_merge(
                $this->getMetadata(),
                [
                    ActorCapable::META_ACTOR_ID => $id,
                ]
            )
        );
    }

    /**
     * @return static
     */
    final public function withActorName(string $name = null): Message
    {
        return $this->withMetadata(
            \array_merge(
                $this->getMetadata(),
                [
                    ActorCapable::META_ACTOR_NAME => $name,
                ]
            )
        );
    }

    final public function getActorId(): string
    {
        return $this->getMetadataKeyValueOrDefault(ActorCapable::META_ACTOR_ID, '');
    }

    final public function getActorName(): string
    {
        return $this->getMetadataKeyValueOrDefault(ActorCapable::META_ACTOR_NAME, '');
    }
}
