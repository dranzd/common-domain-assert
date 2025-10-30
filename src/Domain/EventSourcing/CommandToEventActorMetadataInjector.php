<?php

namespace Dranzd\Common\EventSourcing\Domain\EventSourcing;

use Dranzd\Common\EventSourcing\Domain\Message\Command;
use Dranzd\Common\EventSourcing\Domain\Message\ActorCapable;

final class CommandToEventActorMetadataInjector implements AggregateEventMetadataInjector
{
    public static function inject(AggregateEvent $event, Command $command): AggregateEvent
    {
        $eventMetadata = $event->getMetadata();

        $commandActorId = $command->getMetadata()[ActorCapable::META_ACTOR_ID] ?? '';
        $eventMetadata[ActorCapable::META_ACTOR_ID] = $commandActorId;

        $commandActorName = $command->getMetadata()[ActorCapable::META_ACTOR_NAME] ?? '';
        $eventMetadata[ActorCapable::META_ACTOR_NAME] = $commandActorName;

        return $event->withMetadata($eventMetadata);
    }
}
