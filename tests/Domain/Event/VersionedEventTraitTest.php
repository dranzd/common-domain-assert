<?php

namespace Dranzd\Common\EventSourcing\Tests\Domain\Event;

use Dranzd\Common\EventSourcing\Domain\Event\AbstractEvent;
use Dranzd\Common\EventSourcing\Domain\Event\VersionedEvent;
use Dranzd\Common\EventSourcing\Domain\Event\VersionedEventTrait;
use PHPUnit\Framework\TestCase;

class TestVersionedEventV1 extends AbstractEvent implements VersionedEvent
{
    use VersionedEventTrait;

    const MESSAGE_NAME = 'test.versioned.event';
    const CURRENT_VERSION = 1;

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

class TestVersionedEventV3 extends AbstractEvent implements VersionedEvent
{
    use VersionedEventTrait;

    const MESSAGE_NAME = 'test.versioned.event.v3';
    const CURRENT_VERSION = 3;

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

class TestVersionedEventNoConstant extends AbstractEvent implements VersionedEvent
{
    use VersionedEventTrait;

    const MESSAGE_NAME = 'test.versioned.event.no.constant';

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

final class VersionedEventTraitTest extends TestCase
{
    public function testGetLatestVersionReturnsConstantValue()
    {
        $this->assertEquals(1, TestVersionedEventV1::getLatestVersion());
        $this->assertEquals(3, TestVersionedEventV3::getLatestVersion());
    }

    public function testGetLatestVersionReturnsOneWhenNoConstant()
    {
        $this->assertEquals(1, TestVersionedEventNoConstant::getLatestVersion());
    }

    public function testGetEventVersionReturnsLatestVersionByDefault()
    {
        $event = new TestVersionedEventV3();

        $this->assertEquals(3, $event->getEventVersion());
    }

    public function testGetEventVersionReadsFromMetadata()
    {
        $event = new TestVersionedEventV3();
        $event = $event->withMetadata(['_aggregate_event_version' => 1]);

        $this->assertEquals(1, $event->getEventVersion());
        $this->assertEquals(3, TestVersionedEventV3::getLatestVersion());
    }

    public function testNeedsUpcastingReturnsFalseWhenVersionsMatch()
    {
        $event = new TestVersionedEventV3();

        $this->assertFalse($event->needsUpcasting());
    }

    public function testNeedsUpcastingReturnsTrueWhenVersionIsBehind()
    {
        $event = new TestVersionedEventV3();
        $event = $event->withMetadata(['_aggregate_event_version' => 1]);

        $this->assertTrue($event->needsUpcasting());
    }

    public function testVersionDifferenceReturnsZeroWhenVersionsMatch()
    {
        $event = new TestVersionedEventV3();

        $this->assertEquals(0, $event->versionDifference());
    }

    public function testVersionDifferenceReturnsCorrectDifference()
    {
        $event = new TestVersionedEventV3();
        $event = $event->withMetadata(['_aggregate_event_version' => 1]);

        $this->assertEquals(2, $event->versionDifference());
    }

    public function testVersionDifferenceWithMultipleVersionsBehind()
    {
        $event = new TestVersionedEventV3();
        $event = $event->withMetadata(['_aggregate_event_version' => 2]);

        $this->assertEquals(1, $event->versionDifference());
        $this->assertTrue($event->needsUpcasting());
    }

    public function testEventWithNoConstantDefaultsToVersionOne()
    {
        $event = new TestVersionedEventNoConstant();

        $this->assertEquals(1, $event->getEventVersion());
        $this->assertEquals(1, TestVersionedEventNoConstant::getLatestVersion());
        $this->assertFalse($event->needsUpcasting());
        $this->assertEquals(0, $event->versionDifference());
    }

    public function testGetEventVersionWithDifferentMetadataValues()
    {
        $event = new TestVersionedEventV3();

        // Test with version 0
        $event0 = $event->withMetadata(['_aggregate_event_version' => 0]);
        $this->assertEquals(0, $event0->getEventVersion());
        $this->assertEquals(3, $event0->versionDifference());

        // Test with version 2
        $event2 = $event->withMetadata(['_aggregate_event_version' => 2]);
        $this->assertEquals(2, $event2->getEventVersion());
        $this->assertEquals(1, $event2->versionDifference());

        // Test with version 3 (current)
        $event3 = $event->withMetadata(['_aggregate_event_version' => 3]);
        $this->assertEquals(3, $event3->getEventVersion());
        $this->assertEquals(0, $event3->versionDifference());
        $this->assertFalse($event3->needsUpcasting());
    }
}
