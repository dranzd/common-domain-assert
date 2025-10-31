<?php

namespace Dranzd\Common\EventSourcing\Tests\Domain\EventSourcing;

use Dranzd\Common\EventSourcing\Domain\EventSourcing\AbstractAggregateEvent;
use Dranzd\Common\EventSourcing\Domain\EventSourcing\AggregateEvent;
use Dranzd\Common\EventSourcing\Domain\Message\PayloadContainerCapableTrait;
use PHPUnit\Framework\TestCase;

class TestAggregateEvent extends AbstractAggregateEvent
{
    use PayloadContainerCapableTrait;

    const MESSAGE_NAME = 'test.aggregate.event';
}

final class AbstractAggregateEventTest extends TestCase
{
    public function testGetAggregateRootUuidReturnsEmptyStringByDefault()
    {
        $event = new TestAggregateEvent();

        $this->assertEquals('', $event->getAggregateRootUuid());
    }

    public function testGetAggregateRootUuidReturnsValueFromMetadata()
    {
        $event = new TestAggregateEvent();
        $uuid = 'aggregate-uuid-123';
        $event = $event->withMetadata([
            AggregateEvent::META_AGGREGATE_ROOT_UUID => $uuid,
        ]);

        $this->assertEquals($uuid, $event->getAggregateRootUuid());
    }

    public function testGetAggregateRootTypeReturnsEmptyStringByDefault()
    {
        $event = new TestAggregateEvent();

        $this->assertEquals('', $event->getAggregateRootType());
    }

    public function testGetAggregateRootTypeReturnsValueFromMetadata()
    {
        $event = new TestAggregateEvent();
        $type = 'Order';
        $event = $event->withMetadata([
            AggregateEvent::META_AGGREGATE_ROOT_TYPE => $type,
        ]);

        $this->assertEquals($type, $event->getAggregateRootType());
    }

    public function testGetAggregateRootVersionReturnsZeroByDefault()
    {
        $event = new TestAggregateEvent();

        $this->assertEquals(0, $event->getAggregateRootVersion());
    }

    public function testGetAggregateRootVersionReturnsValueFromMetadata()
    {
        $event = new TestAggregateEvent();
        $version = 5;
        $event = $event->withMetadata([
            AggregateEvent::META_AGGREGATE_ROOT_VERSION => $version,
        ]);

        $this->assertEquals($version, $event->getAggregateRootVersion());
    }

    public function testGetAggregateEventVersionReturnsZeroByDefault()
    {
        $event = new TestAggregateEvent();

        $this->assertEquals(0, $event->getAggregateEventVersion());
    }

    public function testGetAggregateEventVersionReturnsValueFromMetadata()
    {
        $event = new TestAggregateEvent();
        $version = 2;
        $event = $event->withMetadata([
            AggregateEvent::META_AGGREGATE_EVENT_VERSION => $version,
        ]);

        $this->assertEquals($version, $event->getAggregateEventVersion());
    }

    public function testActorCapableTraitMethods()
    {
        $event = new TestAggregateEvent();

        // Default values
        $this->assertEquals('', $event->getActorId());
        $this->assertEquals('', $event->getActorName());

        // With actor ID
        $eventWithActor = $event->withActorId('user-123');
        $this->assertEquals('user-123', $eventWithActor->getActorId());

        // With actor name
        $eventWithName = $event->withActorName('John Doe');
        $this->assertEquals('John Doe', $eventWithName->getActorName());

        // Chain both
        $eventWithBoth = $event
            ->withActorId('user-456')
            ->withActorName('Jane Smith');
        $this->assertEquals('user-456', $eventWithBoth->getActorId());
        $this->assertEquals('Jane Smith', $eventWithBoth->getActorName());
    }

    public function testCausationCapableTraitMethods()
    {
        $event = new TestAggregateEvent();

        // Default values
        $this->assertEquals('', $event->getCausationId());
        $this->assertEquals('', $event->getCausationName());

        // With causation ID
        $eventWithCausation = $event->withCausationId('command-uuid-123');
        $this->assertEquals('command-uuid-123', $eventWithCausation->getCausationId());

        // With causation name
        $eventWithName = $event->withCausationName('CreateOrder');
        $this->assertEquals('CreateOrder', $eventWithName->getCausationName());

        // Chain both
        $eventWithBoth = $event
            ->withCausationId('command-uuid-456')
            ->withCausationName('UpdateOrder');
        $this->assertEquals('command-uuid-456', $eventWithBoth->getCausationId());
        $this->assertEquals('UpdateOrder', $eventWithBoth->getCausationName());
    }

    public function testCorrelationCapableTraitMethods()
    {
        $event = new TestAggregateEvent();

        // Default value
        $this->assertEquals('', $event->getCorrelationId());

        // With correlation ID
        $eventWithCorrelation = $event->withCorrelationId('correlation-uuid-789');
        $this->assertEquals('correlation-uuid-789', $eventWithCorrelation->getCorrelationId());
    }

    public function testAllMetadataCanBeCombined()
    {
        $event = new TestAggregateEvent();
        $enrichedEvent = $event
            ->withMetadata([
                AggregateEvent::META_AGGREGATE_ROOT_UUID => 'agg-uuid',
                AggregateEvent::META_AGGREGATE_ROOT_TYPE => 'Order',
                AggregateEvent::META_AGGREGATE_ROOT_VERSION => 3,
                AggregateEvent::META_AGGREGATE_EVENT_VERSION => 1,
            ])
            ->withActorId('user-123')
            ->withActorName('John Doe')
            ->withCausationId('command-uuid')
            ->withCausationName('CreateOrder')
            ->withCorrelationId('correlation-uuid');

        $this->assertEquals('agg-uuid', $enrichedEvent->getAggregateRootUuid());
        $this->assertEquals('Order', $enrichedEvent->getAggregateRootType());
        $this->assertEquals(3, $enrichedEvent->getAggregateRootVersion());
        $this->assertEquals(1, $enrichedEvent->getAggregateEventVersion());
        $this->assertEquals('user-123', $enrichedEvent->getActorId());
        $this->assertEquals('John Doe', $enrichedEvent->getActorName());
        $this->assertEquals('command-uuid', $enrichedEvent->getCausationId());
        $this->assertEquals('CreateOrder', $enrichedEvent->getCausationName());
        $this->assertEquals('correlation-uuid', $enrichedEvent->getCorrelationId());
    }

    public function testAggregateEventConstants()
    {
        $this->assertEquals('_aggregate_root_uuid', AggregateEvent::META_AGGREGATE_ROOT_UUID);
        $this->assertEquals('_aggregate_root_type', AggregateEvent::META_AGGREGATE_ROOT_TYPE);
        $this->assertEquals('_aggregate_root_version', AggregateEvent::META_AGGREGATE_ROOT_VERSION);
        $this->assertEquals('_aggregate_event_version', AggregateEvent::META_AGGREGATE_EVENT_VERSION);
    }
}
