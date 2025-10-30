<?php

namespace Dranzd\Common\EventSourcing\Domain\EventSourcing;

use Dranzd\Common\EventSourcing\Domain\Message\Command;

interface AggregateEventMetadataInjector
{
    /**
     * @return AggregateEventMetadataInjector
     */
    public static function inject(AggregateEvent $event, Command $command): AggregateEvent;
}
