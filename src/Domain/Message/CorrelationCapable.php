<?php

declare(strict_types=1);

namespace Dranzd\Common\EventSourcing\Domain\Message;

interface CorrelationCapable
{
    public const META_CORRELATION_ID = '_correlation_id';

    public function setCorrelationId(string $id = null): void;

    public function withCorrelationId(string $id = null): Message;

    public function getCorrelationId(): string;
}
