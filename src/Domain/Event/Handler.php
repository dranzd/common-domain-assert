<?php

namespace Dranzd\Common\EventSourcing\Domain\Event;

interface Handler
{
    public function handle(Event $event): void;
}
