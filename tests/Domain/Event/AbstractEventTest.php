<?php

namespace Dranzd\Common\EventSourcing\Tests\Domain\Event;

use Dranzd\Common\EventSourcing\Domain\Event\AbstractEvent;
use PHPUnit\Framework\TestCase;

class TestEvent extends AbstractEvent
{
    const MESSAGE_NAME = 'test.event.occurred';

    private $payload = [];

    public function getPayload(): array
    {
        return $this->payload;
    }

    protected function setPayload(array $payload): void
    {
        $this->payload = $payload;
    }
}

class TestEventWithoutConstant extends AbstractEvent
{
    private $payload = [];

    public function getPayload(): array
    {
        return $this->payload;
    }

    protected function setPayload(array $payload): void
    {
        $this->payload = $payload;
    }
}

final class AbstractEventTest extends TestCase
{
    public function testConstructorGeneratesUuidAndSetsMessageName()
    {
        $event = new TestEvent();

        $this->assertNotEmpty($event->getMessageUuid());
        $this->assertEquals('test.event.occurred', $event->getMessageName());
        $this->assertInstanceOf(\DateTimeImmutable::class, $event->getCreatedAt());
    }

    public function testGetEventNameReturnsMessageName()
    {
        $event = new TestEvent();

        $this->assertEquals($event->getMessageName(), $event->getEventName());
        $this->assertEquals('test.event.occurred', $event->getEventName());
    }

    public function testOccurredAtReturnsCreatedAt()
    {
        $event = new TestEvent();

        $this->assertEquals($event->getCreatedAt(), $event->occurredAt());
        $this->assertInstanceOf(\DateTimeImmutable::class, $event->occurredAt());
    }

    public function testEventIdReturnsMessageUuid()
    {
        $event = new TestEvent();

        $this->assertEquals($event->getMessageUuid(), $event->eventId());
        $this->assertNotEmpty($event->eventId());
    }

    public function testHasMetadataReturnsFalseForNonExistentKey()
    {
        $event = new TestEvent();

        $this->assertFalse($event->hasMetadata('non_existent_key'));
    }

    public function testHasMetadataReturnsTrueForExistingKey()
    {
        $event = new TestEvent();
        $event = $event->withMetadata(['test_key' => 'test_value']);

        $this->assertTrue($event->hasMetadata('test_key'));
    }

    public function testMetadataReturnsValueForExistingKey()
    {
        $event = new TestEvent();
        $event = $event->withMetadata(['test_key' => 'test_value']);

        $this->assertEquals('test_value', $event->metadata('test_key'));
    }

    public function testMetadataReturnsNullForNonExistentKey()
    {
        $event = new TestEvent();

        $this->assertNull($event->metadata('non_existent_key'));
    }

    public function testWithAddedMetadataAddsNewMetadata()
    {
        $event = new TestEvent();
        $event = $event->withMetadata(['existing' => 'value']);
        $newEvent = $event->withAddedMetadata('new_key', 'new_value');

        $this->assertEquals('value', $newEvent->metadata('existing'));
        $this->assertEquals('new_value', $newEvent->metadata('new_key'));
        $this->assertNotSame($event, $newEvent);
    }

    public function testWithAddedMetadataCanAddCausationId()
    {
        $event = new TestEvent();
        $causationId = 'causation-uuid-123';
        $newEvent = $event->withAddedMetadata('causation_id', $causationId);

        $this->assertEquals($causationId, $newEvent->metadata('causation_id'));
        $this->assertEquals($causationId, $newEvent->causationId());
        $this->assertNotSame($event, $newEvent);
    }

    public function testWithAddedMetadataCanAddCorrelationId()
    {
        $event = new TestEvent();
        $correlationId = 'correlation-uuid-456';
        $newEvent = $event->withAddedMetadata('correlation_id', $correlationId);

        $this->assertEquals($correlationId, $newEvent->metadata('correlation_id'));
        $this->assertEquals($correlationId, $newEvent->correlationId());
        $this->assertNotSame($event, $newEvent);
    }

    public function testCausationIdReturnsNullWhenNotSet()
    {
        $event = new TestEvent();

        $this->assertNull($event->causationId());
    }

    public function testCorrelationIdReturnsNullWhenNotSet()
    {
        $event = new TestEvent();

        $this->assertNull($event->correlationId());
    }

    public function testWithAddedMetadataCanBeChainedForCausationAndCorrelation()
    {
        $event = new TestEvent();
        $newEvent = $event
            ->withAddedMetadata('causation_id', 'causation-123')
            ->withAddedMetadata('correlation_id', 'correlation-456');

        $this->assertEquals('causation-123', $newEvent->causationId());
        $this->assertEquals('correlation-456', $newEvent->correlationId());
    }

    public function testExpectedMessageNameUsesConstant()
    {
        $this->assertEquals('test.event.occurred', TestEvent::expectedMessageName());
    }

    public function testExpectedMessageNameUsesClassNameWhenNoConstant()
    {
        $expected = TestEventWithoutConstant::class;
        $this->assertEquals($expected, TestEventWithoutConstant::expectedMessageName());
    }

    public function testEventIsMessageTypeEvent()
    {
        $event = new TestEvent();

        $this->assertTrue($event->isMessageTypeEvent());
        $this->assertFalse($event->isMessageTypeCommand());
        $this->assertFalse($event->isMessageTypeQuery());
        $this->assertFalse($event->isMessageTypeUnknown());
    }

    public function testMultipleEventsHaveUniqueUuids()
    {
        $event1 = new TestEvent();
        $event2 = new TestEvent();

        $this->assertNotEquals($event1->eventId(), $event2->eventId());
    }

    public function testWithAddedMetadataPreservesExistingMetadata()
    {
        $event = new TestEvent();
        $event = $event->withMetadata(['key1' => 'value1', 'key2' => 'value2']);
        $newEvent = $event->withAddedMetadata('key3', 'value3');

        $this->assertEquals('value1', $newEvent->metadata('key1'));
        $this->assertEquals('value2', $newEvent->metadata('key2'));
        $this->assertEquals('value3', $newEvent->metadata('key3'));
    }
}
