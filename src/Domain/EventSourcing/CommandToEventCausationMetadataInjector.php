<?php

namespace Dranzd\Common\EventSourcing\Domain\EventSourcing;

use Dranzd\Common\EventSourcing\Domain\Message\CausationCapable;
use Dranzd\Common\EventSourcing\Domain\Message\Command;

final class CommandToEventCausationMetadataInjector implements AggregateEventMetadataInjector
{
    public static function inject(AggregateEvent $event, Command $command): AggregateEvent
    {
        $eventMetadata = $event->getMetadata();

        $commandCausationId = $command->getMetadata()[CausationCapable::META_CAUSATION_ID] ?? '';
        $eventMetadata[CausationCapable::META_CAUSATION_ID] = $commandCausationId;

        $commandCausationName = $command->getMetadata()[CausationCapable::META_CAUSATION_NAME] ?? '';
        $eventMetadata[CausationCapable::META_CAUSATION_NAME] = $commandCausationName;

        return $event->withMetadata($eventMetadata);
    }
}
