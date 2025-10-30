<?php

namespace Dranzd\Common\EventSourcing\Domain\EventSourcing;

use Dranzd\Common\EventSourcing\Domain\Message\Command;
use Dranzd\Common\EventSourcing\Domain\Message\CorrelationCapable;

final class CommandToEventCorrelationMetadataInjector implements AggregateEventMetadataInjector
{
    public static function inject(AggregateEvent $event, Command $command): AggregateEvent
    {
        $eventMetadata = $event->getMetadata();

        $commandCorrelationId = $command->getMetadata()[CorrelationCapable::META_CORRELATION_ID] ?? '';
        $eventMetadata[CorrelationCapable::META_CORRELATION_ID] = $commandCorrelationId;

        return $event->withMetadata($eventMetadata);
    }
}
