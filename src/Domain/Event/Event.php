<?php

namespace Dranzd\Common\EventSourcing\Domain\Event;

use Dranzd\Common\EventSourcing\Domain\Message\Message;

interface Event extends Message
{
    public function getEventName(): string;
}
