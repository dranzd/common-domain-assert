<?php

namespace Dranzd\Common\EventSourcing\Domain\Event;

use Dranzd\Common\EventSourcing\Domain\Message\Event as MessageEvent;

interface Event extends MessageEvent
{
    public function getEventName(): string;
}
