<?php

namespace Dranzd\Common\EventSourcing\Domain\EventSourcing;

use Dranzd\Common\EventSourcing\Domain\Message\Command;
use Dranzd\Common\EventSourcing\Util\ObjectUtil;

final class CorrelationMetadataInjectorVisitor implements AggregateVisitor
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
            throw new \DomainException('Expecting command to correlate to but none was set');
        }

        $events = ObjectUtil::instance()->valueOfProperty($aggregateRoot, 'aggregateRecordedEvents');

        $events = \array_map(
            function ($event) {
                if (!\method_exists($event, 'getCorrelationId')) {
                    return $event;
                }

                $id = $event->getCorrelationId();
                if ($id) {
                    return $event;
                }

                return CommandToEventCorrelationMetadataInjector::inject($event, $this->command);
            },
            $events
        );

        ObjectUtil::instance()->setPropertyValue($aggregateRoot, 'aggregateRecordedEvents', $events);
    }
}
