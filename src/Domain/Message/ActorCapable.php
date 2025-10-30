<?php

declare(strict_types=1);

namespace Dranzd\Common\EventSourcing\Domain\Message;

interface ActorCapable
{
    public const META_ACTOR_ID = '_actor_id';
    public const META_ACTOR_NAME = '_actor_name';

    public function setActorId(string $id = null): void;

    public function setActorName(string $name = null): void;

    public function withActorId(string $id = null): Message;

    public function withActorName(string $name = null): Message;

    public function getActorId(): string;

    public function getActorName(): string;
}
