<?php

namespace Dranzd\Common\EventSourcing\Domain\Message;

interface CausationCapable
{
    public const META_CAUSATION_ID   = '_causation_id';
    public const META_CAUSATION_NAME = '_causation_name';

    public function setCausationId(string $id = null): void;

    public function setCausationName(string $name = null): void;

    public function withCausationId(string $id = null): Message;

    public function withCausationName(string $name = null): Message;

    public function getCausationId(): string;

    public function getCausationName(): string;
}
