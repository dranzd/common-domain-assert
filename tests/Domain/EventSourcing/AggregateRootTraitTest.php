<?php

namespace Dranzd\Common\EventSourcing\Tests\Domain\EventSourcing;

use Dranzd\Common\EventSourcing\Domain\EventSourcing\AbstractAggregateEvent;
use Dranzd\Common\EventSourcing\Domain\EventSourcing\AggregateEvent;
use Dranzd\Common\EventSourcing\Domain\EventSourcing\AggregateRoot;
use Dranzd\Common\EventSourcing\Domain\EventSourcing\AggregateRootTrait;
use Dranzd\Common\EventSourcing\Domain\Message\PayloadContainerCapableTrait;
use PHPUnit\Framework\TestCase;

class OrderCreated extends AbstractAggregateEvent
{
    use PayloadContainerCapableTrait;

    const MESSAGE_NAME = 'order.created';
}

class OrderUpdated extends AbstractAggregateEvent
{
    use PayloadContainerCapableTrait;

    const MESSAGE_NAME = 'order.updated';
}

class OrderCancelled extends AbstractAggregateEvent
{
    use PayloadContainerCapableTrait;

    const MESSAGE_NAME = 'order.cancelled';
}

class TestOrder implements AggregateRoot
{
    use AggregateRootTrait;

    private $uuid;
    private $status;
    private $items = [];

    public function __construct(string $uuid = null)
    {
        $this->uuid = $uuid ?? 'order-uuid-123';
    }

    public function getAggregateRootUuid(): string
    {
        return $this->uuid;
    }

    public function createOrder(array $items): void
    {
        $event = new OrderCreated();
        $event = $event->withPayload(['items' => $items]);
        $this->recordThat($event);
    }

    public function updateOrder(array $items): void
    {
        $event = new OrderUpdated();
        $event = $event->withPayload(['items' => $items]);
        $this->recordThat($event);
    }

    public function cancelOrder(): void
    {
        $event = new OrderCancelled();
        $this->recordThat($event);
    }

    protected function applyOnOrderCreated(OrderCreated $event): void
    {
        $this->status = 'created';
        $this->items = $event->getPayload()['items'] ?? [];
    }

    protected function applyOnOrderUpdated(OrderUpdated $event): void
    {
        $this->status = 'updated';
        $this->items = $event->getPayload()['items'] ?? [];
    }

    protected function applyOnOrderCancelled(OrderCancelled $event): void
    {
        $this->status = 'cancelled';
    }

    public function getStatus(): string
    {
        return $this->status ?? '';
    }

    public function getItems(): array
    {
        return $this->items;
    }
}

final class AggregateRootTraitTest extends TestCase
{
    public function testInitialVersionIsZero()
    {
        $order = new TestOrder();

        $this->assertEquals(0, $order->getAggregateRootVersion());
    }

    public function testGetAggregateRootTypeReturnsClassName()
    {
        $order = new TestOrder();

        $this->assertEquals(TestOrder::class, $order->getAggregateRootType());
    }

    public function testGetAggregateRootUuidReturnsUuid()
    {
        $uuid = 'custom-uuid-456';
        $order = new TestOrder($uuid);

        $this->assertEquals($uuid, $order->getAggregateRootUuid());
    }

    public function testRecordedEventsStartsEmpty()
    {
        $order = new TestOrder();

        $this->assertCount(0, $order->getRecordedEvents());
    }

    public function testRecordThatAddsEventToRecordedEvents()
    {
        $order = new TestOrder();
        $order->createOrder(['item1', 'item2']);

        $events = $order->getRecordedEvents();

        $this->assertCount(1, $events);
        $this->assertInstanceOf(OrderCreated::class, $events[0]);
    }

    public function testRecordThatIncrementsVersion()
    {
        $order = new TestOrder();

        $this->assertEquals(0, $order->getAggregateRootVersion());

        $order->createOrder(['item1']);
        $this->assertEquals(1, $order->getAggregateRootVersion());

        $order->updateOrder(['item1', 'item2']);
        $this->assertEquals(2, $order->getAggregateRootVersion());

        $order->cancelOrder();
        $this->assertEquals(3, $order->getAggregateRootVersion());
    }

    public function testRecordThatEnrichesEventWithAggregateMetadata()
    {
        $uuid = 'order-uuid-789';
        $order = new TestOrder($uuid);
        $order->createOrder(['item1']);

        $events = $order->getRecordedEvents();
        $event = $events[0];

        $this->assertEquals($uuid, $event->getAggregateRootUuid());
        $this->assertEquals(TestOrder::class, $event->getAggregateRootType());
        $this->assertEquals(1, $event->getAggregateRootVersion());
    }

    public function testRecordThatAppliesEventToAggregate()
    {
        $order = new TestOrder();
        $order->createOrder(['item1', 'item2']);

        $this->assertEquals('created', $order->getStatus());
        $this->assertEquals(['item1', 'item2'], $order->getItems());
    }

    public function testPopRecordedEventsReturnsAndClearsEvents()
    {
        $order = new TestOrder();
        $order->createOrder(['item1']);
        $order->updateOrder(['item1', 'item2']);

        $this->assertCount(2, $order->getRecordedEvents());

        $events = $order->popRecordedEvents();

        $this->assertCount(2, $events);
        $this->assertCount(0, $order->getRecordedEvents());
    }

    public function testPopRecordedEventsDoesNotResetVersion()
    {
        $order = new TestOrder();
        $order->createOrder(['item1']);
        $order->updateOrder(['item1', 'item2']);

        $this->assertEquals(2, $order->getAggregateRootVersion());

        $order->popRecordedEvents();

        $this->assertEquals(2, $order->getAggregateRootVersion());
    }

    public function testReconstituteFromHistoryCreatesNewInstance()
    {
        $order = new TestOrder('original-uuid');
        $order->createOrder(['item1']);

        $events = $order->getRecordedEvents();

        $reconstituted = $order->reconstituteFromHistory($events);

        $this->assertNotSame($order, $reconstituted);
        $this->assertInstanceOf(TestOrder::class, $reconstituted);
    }

    public function testReconstituteFromHistoryReplaysEvents()
    {
        $order = new TestOrder();
        $order->createOrder(['item1']);
        $order->updateOrder(['item1', 'item2', 'item3']);

        $events = $order->popRecordedEvents();

        $reconstituted = $order->reconstituteFromHistory($events);

        $this->assertEquals('updated', $reconstituted->getStatus());
        $this->assertEquals(['item1', 'item2', 'item3'], $reconstituted->getItems());
        $this->assertEquals(2, $reconstituted->getAggregateRootVersion());
    }

    public function testReconstituteFromHistoryDoesNotRecordEvents()
    {
        $order = new TestOrder();
        $order->createOrder(['item1']);

        $events = $order->getRecordedEvents();

        $reconstituted = $order->reconstituteFromHistory($events);

        $this->assertCount(0, $reconstituted->getRecordedEvents());
    }

    public function testApplyThrowsExceptionForMissingMethod()
    {
        $this->expectException(\BadMethodCallException::class);
        $this->expectExceptionMessage('Call to method');

        $order = new TestOrder();

        // Create an event that doesn't have a corresponding applyOn method
        $event = new class extends AbstractAggregateEvent {
            use PayloadContainerCapableTrait;
            const MESSAGE_NAME = 'unknown.event';
        };

        // Use reflection to call the private apply method
        $reflection = new \ReflectionClass($order);
        $method = $reflection->getMethod('apply');
        $method->setAccessible(true);
        $method->invoke($order, $event);
    }

    public function testMultipleEventsInSequence()
    {
        $order = new TestOrder();

        $order->createOrder(['item1']);
        $this->assertEquals(1, $order->getAggregateRootVersion());
        $this->assertEquals('created', $order->getStatus());

        $order->updateOrder(['item1', 'item2']);
        $this->assertEquals(2, $order->getAggregateRootVersion());
        $this->assertEquals('updated', $order->getStatus());

        $order->cancelOrder();
        $this->assertEquals(3, $order->getAggregateRootVersion());
        $this->assertEquals('cancelled', $order->getStatus());

        $events = $order->getRecordedEvents();
        $this->assertCount(3, $events);
        $this->assertInstanceOf(OrderCreated::class, $events[0]);
        $this->assertInstanceOf(OrderUpdated::class, $events[1]);
        $this->assertInstanceOf(OrderCancelled::class, $events[2]);
    }

    public function testEventMetadataIsPreservedDuringReconstitution()
    {
        $order = new TestOrder('order-123');
        $order->createOrder(['item1']);

        $events = $order->getRecordedEvents();
        $event = $events[0];

        // Verify metadata was added during recording
        $this->assertEquals('order-123', $event->getAggregateRootUuid());
        $this->assertEquals(TestOrder::class, $event->getAggregateRootType());
        $this->assertEquals(1, $event->getAggregateRootVersion());

        // Reconstitute and verify metadata is preserved
        $reconstituted = $order->reconstituteFromHistory($events);
        $this->assertEquals(1, $reconstituted->getAggregateRootVersion());
    }
}
