<?php

namespace Dranzd\Common\EventSourcing\Tests\Domain\Message;

use Dranzd\Common\EventSourcing\Domain\Event\AbstractEvent;
use Dranzd\Common\EventSourcing\Domain\Message\ActorCapable;
use Dranzd\Common\EventSourcing\Domain\Message\ActorCapableTrait;
use Dranzd\Common\EventSourcing\Domain\Message\CausationCapable;
use Dranzd\Common\EventSourcing\Domain\Message\CausationCapableTrait;
use Dranzd\Common\EventSourcing\Domain\Message\CorrelationCapable;
use Dranzd\Common\EventSourcing\Domain\Message\CorrelationCapableTrait;
use Dranzd\Common\EventSourcing\Domain\Message\PayloadContainerCapableTrait;
use PHPUnit\Framework\TestCase;

class EventWithCausation extends AbstractEvent implements CausationCapable
{
    use CausationCapableTrait;
    use PayloadContainerCapableTrait;

    const MESSAGE_NAME = 'event.with.causation';
}

class EventWithCorrelation extends AbstractEvent implements CorrelationCapable
{
    use CorrelationCapableTrait;
    use PayloadContainerCapableTrait;

    const MESSAGE_NAME = 'event.with.correlation';
}

class EventWithBoth extends AbstractEvent implements CausationCapable, CorrelationCapable
{
    use CausationCapableTrait, CorrelationCapableTrait;
    use PayloadContainerCapableTrait;

    const MESSAGE_NAME = 'event.with.both';
}

class EventWithAll extends AbstractEvent implements CausationCapable, CorrelationCapable, ActorCapable
{
    use CausationCapableTrait, CorrelationCapableTrait, ActorCapableTrait;
    use PayloadContainerCapableTrait;

    const MESSAGE_NAME = 'event.with.all';
}

final class CausationAndCorrelationCapableTest extends TestCase
{
    public function testCausationCapableGetCausationIdReturnsEmptyByDefault()
    {
        $event = new EventWithCausation();

        $this->assertEquals('', $event->getCausationId());
    }

    public function testCausationCapableGetCausationNameReturnsEmptyByDefault()
    {
        $event = new EventWithCausation();

        $this->assertEquals('', $event->getCausationName());
    }

    public function testCausationCapableWithCausationIdSetsCausationId()
    {
        $event = new EventWithCausation();
        $causationId = 'command-uuid-123';

        $newEvent = $event->withCausationId($causationId);

        $this->assertEquals($causationId, $newEvent->getCausationId());
        $this->assertNotSame($event, $newEvent);
    }

    public function testCausationCapableWithCausationNameSetsCausationName()
    {
        $event = new EventWithCausation();
        $causationName = 'CreateOrder';

        $newEvent = $event->withCausationName($causationName);

        $this->assertEquals($causationName, $newEvent->getCausationName());
        $this->assertNotSame($event, $newEvent);
    }

    public function testCausationCapableCanChainBothMethods()
    {
        $event = new EventWithCausation();

        $newEvent = $event
            ->withCausationId('command-uuid-456')
            ->withCausationName('UpdateOrder');

        $this->assertEquals('command-uuid-456', $newEvent->getCausationId());
        $this->assertEquals('UpdateOrder', $newEvent->getCausationName());
    }

    public function testCausationCapableMetadataConstants()
    {
        $this->assertEquals('_causation_id', CausationCapable::META_CAUSATION_ID);
        $this->assertEquals('_causation_name', CausationCapable::META_CAUSATION_NAME);
    }

    public function testCorrelationCapableGetCorrelationIdReturnsEmptyByDefault()
    {
        $event = new EventWithCorrelation();

        $this->assertEquals('', $event->getCorrelationId());
    }

    public function testCorrelationCapableWithCorrelationIdSetsCorrelationId()
    {
        $event = new EventWithCorrelation();
        $correlationId = 'correlation-uuid-789';

        $newEvent = $event->withCorrelationId($correlationId);

        $this->assertEquals($correlationId, $newEvent->getCorrelationId());
        $this->assertNotSame($event, $newEvent);
    }

    public function testCorrelationCapableMetadataConstant()
    {
        $this->assertEquals('_correlation_id', CorrelationCapable::META_CORRELATION_ID);
    }

    public function testEventWithBothTraitsCanUseBoth()
    {
        $event = new EventWithBoth();

        $newEvent = $event
            ->withCausationId('causation-123')
            ->withCausationName('CreateOrder')
            ->withCorrelationId('correlation-456');

        $this->assertEquals('causation-123', $newEvent->getCausationId());
        $this->assertEquals('CreateOrder', $newEvent->getCausationName());
        $this->assertEquals('correlation-456', $newEvent->getCorrelationId());
    }

    public function testEventWithAllTraitsCanUseAll()
    {
        $event = new EventWithAll();

        $newEvent = $event
            ->withCausationId('causation-123')
            ->withCausationName('CreateOrder')
            ->withCorrelationId('correlation-456')
            ->withActorId('user-789')
            ->withActorName('John Doe');

        $this->assertEquals('causation-123', $newEvent->getCausationId());
        $this->assertEquals('CreateOrder', $newEvent->getCausationName());
        $this->assertEquals('correlation-456', $newEvent->getCorrelationId());
        $this->assertEquals('user-789', $newEvent->getActorId());
        $this->assertEquals('John Doe', $newEvent->getActorName());
    }

    public function testCausationCapableMetadataIsStoredCorrectly()
    {
        $event = new EventWithCausation();
        $event = $event
            ->withCausationId('cmd-uuid')
            ->withCausationName('TestCommand');

        $metadata = $event->getMetadata();

        $this->assertEquals('cmd-uuid', $metadata[CausationCapable::META_CAUSATION_ID]);
        $this->assertEquals('TestCommand', $metadata[CausationCapable::META_CAUSATION_NAME]);
    }

    public function testCorrelationCapableMetadataIsStoredCorrectly()
    {
        $event = new EventWithCorrelation();
        $event = $event->withCorrelationId('corr-uuid');

        $metadata = $event->getMetadata();

        $this->assertEquals('corr-uuid', $metadata[CorrelationCapable::META_CORRELATION_ID]);
    }

    public function testWithMethodsPreserveOtherMetadata()
    {
        $event = new EventWithBoth();
        $event = $event->withMetadata(['custom_key' => 'custom_value']);

        $newEvent = $event
            ->withCausationId('causation-id')
            ->withCorrelationId('correlation-id');

        $this->assertEquals('custom_value', $newEvent->getMetadata()['custom_key']);
        $this->assertEquals('causation-id', $newEvent->getCausationId());
        $this->assertEquals('correlation-id', $newEvent->getCorrelationId());
    }

    public function testImmutabilityOfWithMethods()
    {
        $event = new EventWithBoth();

        $event1 = $event->withCausationId('id-1');
        $event2 = $event->withCorrelationId('id-2');

        // Original should be unchanged
        $this->assertEquals('', $event->getCausationId());
        $this->assertEquals('', $event->getCorrelationId());

        // Each new instance should have only its own changes
        $this->assertEquals('id-1', $event1->getCausationId());
        $this->assertEquals('', $event1->getCorrelationId());

        $this->assertEquals('', $event2->getCausationId());
        $this->assertEquals('id-2', $event2->getCorrelationId());
    }
}
