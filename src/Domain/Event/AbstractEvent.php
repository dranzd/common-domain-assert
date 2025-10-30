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
            \defined(static::class . '::MESSAGE_NAME') && \constant(static::class . '::MESSAGE_NAME')
                ? \constant(static::class . '::MESSAGE_NAME')
                : \get_class($this),
            []
        );
    }

    final public function getEventName(): string
    {
        return $this->getMessageName();
    }
}
