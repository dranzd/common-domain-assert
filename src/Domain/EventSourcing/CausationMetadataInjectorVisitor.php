<?php

namespace Dranzd\Common\EventSourcing\Domain\EventSourcing;

use Dranzd\Common\EventSourcing\Domain\Message\Command;
use Dranzd\Common\EventSourcing\Util\ObjectUtil;

final class CausationMetadataInjectorVisitor implements AggregateVisitor
{
    /**
     * @var Command
     */
    private $command;

    final public function setCommand(Command $command): void
    {
        $this->command = $command;
    }

    final public function getCommand(): Command
    {
        return $this->command;
    }

    final public function unsetCommand(): void
    {
        $this->command = null;
    }

    final public function visit(AggregateRoot $aggregateRoot): void
    {
        if (!$this->command) {
            throw new \DomainException('Expecting command to set cause of but none was set');
        }

        $events = ObjectUtil::instance()->valueOfProperty($aggregateRoot, 'aggregateRecordedEvents');

        $events = \array_map(
            function ($event) {
                if (!\method_exists($event, 'getCausationId')) {
                    return $event;
                }

                $id = $event->getCausationId();
                if ($id) {
                    return $event;
                }

                return CommandToEventCausationMetadataInjector::inject($event, $this->command);
            },
            $events
        );

        ObjectUtil::instance()->setPropertyValue($aggregateRoot, 'aggregateRecordedEvents', $events);
    }
}
