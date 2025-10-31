<?php

namespace Dranzd\Common\EventSourcing\Tests\Domain\EventSourcing;

use Dranzd\Common\EventSourcing\Domain\EventSourcing\AbstractAggregateEvent;
use Dranzd\Common\EventSourcing\Domain\EventSourcing\CommandToEventActorMetadataInjector;
use Dranzd\Common\EventSourcing\Domain\EventSourcing\CommandToEventCausationMetadataInjector;
use Dranzd\Common\EventSourcing\Domain\EventSourcing\CommandToEventCorrelationMetadataInjector;
use Dranzd\Common\EventSourcing\Domain\Message\ActorCapable;
use Dranzd\Common\EventSourcing\Domain\Message\CausationCapable;
use Dranzd\Common\EventSourcing\Domain\Message\Command;
use Dranzd\Common\EventSourcing\Domain\Message\CorrelationCapable;
use Dranzd\Common\EventSourcing\Domain\Message\GenericMessage;
use Dranzd\Common\EventSourcing\Domain\Message\PayloadContainerCapableTrait;
use PHPUnit\Framework\TestCase;

class TestCommand extends GenericMessage implements Command
{
    use PayloadContainerCapableTrait;

    const MESSAGE_NAME = 'test.command';
}

class TestEvent extends AbstractAggregateEvent
{
    use PayloadContainerCapableTrait;

    const MESSAGE_NAME = 'test.event';
}

final class MetadataInjectorsTest extends TestCase
{
    public function testCommandToEventActorMetadataInjectorCopiesActorId()
    {
        $command = new TestCommand('uuid', 'command', []);
        $command = $command->withMetadata([
            ActorCapable::META_ACTOR_ID => 'user-123',
        ]);

        $event = new TestEvent();

        $enrichedEvent = CommandToEventActorMetadataInjector::inject($event, $command);

        $this->assertEquals('user-123', $enrichedEvent->getMetadata()[ActorCapable::META_ACTOR_ID]);
    }

    public function testCommandToEventActorMetadataInjectorCopiesActorName()
    {
        $command = new TestCommand('uuid', 'command', []);
        $command = $command->withMetadata([
            ActorCapable::META_ACTOR_NAME => 'John Doe',
        ]);

        $event = new TestEvent();

        $enrichedEvent = CommandToEventActorMetadataInjector::inject($event, $command);

        $this->assertEquals('John Doe', $enrichedEvent->getMetadata()[ActorCapable::META_ACTOR_NAME]);
    }

    public function testCommandToEventActorMetadataInjectorCopiesBothActorFields()
    {
        $command = new TestCommand('uuid', 'command', []);
        $command = $command->withMetadata([
            ActorCapable::META_ACTOR_ID => 'user-456',
            ActorCapable::META_ACTOR_NAME => 'Jane Smith',
        ]);

        $event = new TestEvent();

        $enrichedEvent = CommandToEventActorMetadataInjector::inject($event, $command);

        $this->assertEquals('user-456', $enrichedEvent->getMetadata()[ActorCapable::META_ACTOR_ID]);
        $this->assertEquals('Jane Smith', $enrichedEvent->getMetadata()[ActorCapable::META_ACTOR_NAME]);
    }

    public function testCommandToEventActorMetadataInjectorHandlesMissingActorData()
    {
        $command = new TestCommand('uuid', 'command', []);
        $event = new TestEvent();

        $enrichedEvent = CommandToEventActorMetadataInjector::inject($event, $command);

        $this->assertEquals('', $enrichedEvent->getMetadata()[ActorCapable::META_ACTOR_ID]);
        $this->assertEquals('', $enrichedEvent->getMetadata()[ActorCapable::META_ACTOR_NAME]);
    }

    public function testCommandToEventCausationMetadataInjectorCopiesCausationId()
    {
        $command = new TestCommand('uuid', 'command', []);
        $command = $command->withMetadata([
            CausationCapable::META_CAUSATION_ID => 'command-uuid-123',
        ]);

        $event = new TestEvent();

        $enrichedEvent = CommandToEventCausationMetadataInjector::inject($event, $command);

        $this->assertEquals('command-uuid-123', $enrichedEvent->getMetadata()[CausationCapable::META_CAUSATION_ID]);
    }

    public function testCommandToEventCausationMetadataInjectorCopiesCausationName()
    {
        $command = new TestCommand('uuid', 'command', []);
        $command = $command->withMetadata([
            CausationCapable::META_CAUSATION_NAME => 'CreateOrder',
        ]);

        $event = new TestEvent();

        $enrichedEvent = CommandToEventCausationMetadataInjector::inject($event, $command);

        $this->assertEquals('CreateOrder', $enrichedEvent->getMetadata()[CausationCapable::META_CAUSATION_NAME]);
    }

    public function testCommandToEventCausationMetadataInjectorCopiesBothCausationFields()
    {
        $command = new TestCommand('uuid', 'command', []);
        $command = $command->withMetadata([
            CausationCapable::META_CAUSATION_ID => 'command-uuid-456',
            CausationCapable::META_CAUSATION_NAME => 'UpdateOrder',
        ]);

        $event = new TestEvent();

        $enrichedEvent = CommandToEventCausationMetadataInjector::inject($event, $command);

        $this->assertEquals('command-uuid-456', $enrichedEvent->getMetadata()[CausationCapable::META_CAUSATION_ID]);
        $this->assertEquals('UpdateOrder', $enrichedEvent->getMetadata()[CausationCapable::META_CAUSATION_NAME]);
    }

    public function testCommandToEventCausationMetadataInjectorHandlesMissingCausationData()
    {
        $command = new TestCommand('uuid', 'command', []);
        $event = new TestEvent();

        $enrichedEvent = CommandToEventCausationMetadataInjector::inject($event, $command);

        $this->assertEquals('', $enrichedEvent->getMetadata()[CausationCapable::META_CAUSATION_ID]);
        $this->assertEquals('', $enrichedEvent->getMetadata()[CausationCapable::META_CAUSATION_NAME]);
    }

    public function testCommandToEventCorrelationMetadataInjectorCopiesCorrelationId()
    {
        $command = new TestCommand('uuid', 'command', []);
        $command = $command->withMetadata([
            CorrelationCapable::META_CORRELATION_ID => 'correlation-uuid-789',
        ]);

        $event = new TestEvent();

        $enrichedEvent = CommandToEventCorrelationMetadataInjector::inject($event, $command);

        $this->assertEquals('correlation-uuid-789', $enrichedEvent->getMetadata()[CorrelationCapable::META_CORRELATION_ID]);
    }

    public function testCommandToEventCorrelationMetadataInjectorHandlesMissingCorrelationData()
    {
        $command = new TestCommand('uuid', 'command', []);
        $event = new TestEvent();

        $enrichedEvent = CommandToEventCorrelationMetadataInjector::inject($event, $command);

        $this->assertEquals('', $enrichedEvent->getMetadata()[CorrelationCapable::META_CORRELATION_ID]);
    }

    public function testInjectorsPreserveExistingEventMetadata()
    {
        $command = new TestCommand('uuid', 'command', []);
        $command = $command->withMetadata([
            ActorCapable::META_ACTOR_ID => 'user-123',
        ]);

        $event = new TestEvent();
        $event = $event->withMetadata([
            'custom_field' => 'custom_value',
        ]);

        $enrichedEvent = CommandToEventActorMetadataInjector::inject($event, $command);

        $this->assertEquals('custom_value', $enrichedEvent->getMetadata()['custom_field']);
        $this->assertEquals('user-123', $enrichedEvent->getMetadata()[ActorCapable::META_ACTOR_ID]);
    }

    public function testInjectorsCanBeChained()
    {
        $command = new TestCommand('uuid', 'command', []);
        $command = $command->withMetadata([
            ActorCapable::META_ACTOR_ID => 'user-123',
            ActorCapable::META_ACTOR_NAME => 'John Doe',
            CausationCapable::META_CAUSATION_ID => 'command-uuid',
            CausationCapable::META_CAUSATION_NAME => 'CreateOrder',
            CorrelationCapable::META_CORRELATION_ID => 'correlation-uuid',
        ]);

        $event = new TestEvent();

        $enrichedEvent = CommandToEventActorMetadataInjector::inject($event, $command);
        $enrichedEvent = CommandToEventCausationMetadataInjector::inject($enrichedEvent, $command);
        $enrichedEvent = CommandToEventCorrelationMetadataInjector::inject($enrichedEvent, $command);

        $metadata = $enrichedEvent->getMetadata();

        $this->assertEquals('user-123', $metadata[ActorCapable::META_ACTOR_ID]);
        $this->assertEquals('John Doe', $metadata[ActorCapable::META_ACTOR_NAME]);
        $this->assertEquals('command-uuid', $metadata[CausationCapable::META_CAUSATION_ID]);
        $this->assertEquals('CreateOrder', $metadata[CausationCapable::META_CAUSATION_NAME]);
        $this->assertEquals('correlation-uuid', $metadata[CorrelationCapable::META_CORRELATION_ID]);
    }

    public function testInjectorsReturnNewEventInstance()
    {
        $command = new TestCommand('uuid', 'command', []);
        $command = $command->withMetadata([
            ActorCapable::META_ACTOR_ID => 'user-123',
        ]);

        $event = new TestEvent();

        $enrichedEvent = CommandToEventActorMetadataInjector::inject($event, $command);

        $this->assertNotSame($event, $enrichedEvent);
        $this->assertEmpty($event->getMetadata()[ActorCapable::META_ACTOR_ID] ?? '');
        $this->assertEquals('user-123', $enrichedEvent->getMetadata()[ActorCapable::META_ACTOR_ID]);
    }
}
