<?php

namespace Dranzd\Common\EventSourcing\Tests\Domain\Event;

use Dranzd\Common\EventSourcing\Domain\Event\EventUpcaster;
use Dranzd\Common\EventSourcing\Domain\Event\EventUpcasterChain;
use PHPUnit\Framework\TestCase;

class TestUpcasterV1ToV2 implements EventUpcaster
{
    public function canUpcast(string $eventName, int $version): bool
    {
        return $eventName === 'test.event' && $version === 1;
    }

    public function upcast(array $eventData): array
    {
        $eventData['payload']['new_field'] = 'added_in_v2';
        $eventData['metadata']['_aggregate_event_version'] = 2;
        return $eventData;
    }

    public function toVersion(): int
    {
        return 2;
    }
}

class TestUpcasterV2ToV3 implements EventUpcaster
{
    public function canUpcast(string $eventName, int $version): bool
    {
        return $eventName === 'test.event' && $version === 2;
    }

    public function upcast(array $eventData): array
    {
        $eventData['payload']['another_field'] = 'added_in_v3';
        $eventData['metadata']['_aggregate_event_version'] = 3;
        return $eventData;
    }

    public function toVersion(): int
    {
        return 3;
    }
}

class AnotherEventUpcaster implements EventUpcaster
{
    public function canUpcast(string $eventName, int $version): bool
    {
        return $eventName === 'another.event' && $version === 1;
    }

    public function upcast(array $eventData): array
    {
        $eventData['payload']['transformed'] = true;
        $eventData['metadata']['_aggregate_event_version'] = 2;
        return $eventData;
    }

    public function toVersion(): int
    {
        return 2;
    }
}

final class EventUpcasterChainTest extends TestCase
{
    public function testConstructorAcceptsEmptyArray()
    {
        $chain = new EventUpcasterChain([]);

        $this->assertCount(0, $chain->getUpcasters());
    }

    public function testConstructorAcceptsUpcasters()
    {
        $upcaster1 = new TestUpcasterV1ToV2();
        $upcaster2 = new TestUpcasterV2ToV3();

        $chain = new EventUpcasterChain([$upcaster1, $upcaster2]);

        $this->assertCount(2, $chain->getUpcasters());
    }

    public function testAddUpcasterAddsToChain()
    {
        $chain = new EventUpcasterChain();
        $upcaster = new TestUpcasterV1ToV2();

        $result = $chain->addUpcaster($upcaster);

        $this->assertSame($chain, $result);
        $this->assertCount(1, $chain->getUpcasters());
    }

    public function testUpcastWithNoMatchingUpcasterReturnsOriginalData()
    {
        $chain = new EventUpcasterChain([new TestUpcasterV1ToV2()]);

        $eventData = [
            'message_uuid' => 'uuid-123',
            'message_name' => 'unknown.event',
            'created_at' => '2024-01-01T00:00:00.000000+00:00',
            'metadata' => ['_aggregate_event_version' => 1],
            'payload' => ['original' => 'data'],
        ];

        $result = $chain->upcast($eventData);

        $this->assertEquals($eventData, $result);
    }

    public function testUpcastAppliesSingleUpcaster()
    {
        $chain = new EventUpcasterChain([new TestUpcasterV1ToV2()]);

        $eventData = [
            'message_uuid' => 'uuid-123',
            'message_name' => 'test.event',
            'created_at' => '2024-01-01T00:00:00.000000+00:00',
            'metadata' => ['_aggregate_event_version' => 1],
            'payload' => ['original' => 'data'],
        ];

        $result = $chain->upcast($eventData);

        $this->assertEquals(2, $result['metadata']['_aggregate_event_version']);
        $this->assertEquals('added_in_v2', $result['payload']['new_field']);
        $this->assertEquals('data', $result['payload']['original']);
    }

    public function testUpcastAppliesMultipleUpcastersInSequence()
    {
        $chain = new EventUpcasterChain([
            new TestUpcasterV1ToV2(),
            new TestUpcasterV2ToV3(),
        ]);

        $eventData = [
            'message_uuid' => 'uuid-123',
            'message_name' => 'test.event',
            'created_at' => '2024-01-01T00:00:00.000000+00:00',
            'metadata' => ['_aggregate_event_version' => 1],
            'payload' => ['original' => 'data'],
        ];

        $result = $chain->upcast($eventData);

        $this->assertEquals(3, $result['metadata']['_aggregate_event_version']);
        $this->assertEquals('added_in_v2', $result['payload']['new_field']);
        $this->assertEquals('added_in_v3', $result['payload']['another_field']);
        $this->assertEquals('data', $result['payload']['original']);
    }

    public function testUpcastStopsWhenNoMoreUpcastersMatch()
    {
        $chain = new EventUpcasterChain([new TestUpcasterV1ToV2()]);

        $eventData = [
            'message_uuid' => 'uuid-123',
            'message_name' => 'test.event',
            'created_at' => '2024-01-01T00:00:00.000000+00:00',
            'metadata' => ['_aggregate_event_version' => 1],
            'payload' => ['original' => 'data'],
        ];

        $result = $chain->upcast($eventData);

        // Should only upcast to V2, not V3 (no V2->V3 upcaster in chain)
        $this->assertEquals(2, $result['metadata']['_aggregate_event_version']);
        $this->assertArrayNotHasKey('another_field', $result['payload']);
    }

    public function testNeedsUpcastingReturnsTrueWhenUpcasterExists()
    {
        $chain = new EventUpcasterChain([new TestUpcasterV1ToV2()]);

        $this->assertTrue($chain->needsUpcasting('test.event', 1));
    }

    public function testNeedsUpcastingReturnsFalseWhenNoUpcasterExists()
    {
        $chain = new EventUpcasterChain([new TestUpcasterV1ToV2()]);

        $this->assertFalse($chain->needsUpcasting('test.event', 2));
        $this->assertFalse($chain->needsUpcasting('unknown.event', 1));
    }

    public function testUpcastWithDifferentEventTypes()
    {
        $chain = new EventUpcasterChain([
            new TestUpcasterV1ToV2(),
            new AnotherEventUpcaster(),
        ]);

        $eventData1 = [
            'message_uuid' => 'uuid-123',
            'message_name' => 'test.event',
            'created_at' => '2024-01-01T00:00:00.000000+00:00',
            'metadata' => ['_aggregate_event_version' => 1],
            'payload' => [],
        ];

        $eventData2 = [
            'message_uuid' => 'uuid-456',
            'message_name' => 'another.event',
            'created_at' => '2024-01-01T00:00:00.000000+00:00',
            'metadata' => ['_aggregate_event_version' => 1],
            'payload' => [],
        ];

        $result1 = $chain->upcast($eventData1);
        $result2 = $chain->upcast($eventData2);

        $this->assertEquals(2, $result1['metadata']['_aggregate_event_version']);
        $this->assertArrayHasKey('new_field', $result1['payload']);

        $this->assertEquals(2, $result2['metadata']['_aggregate_event_version']);
        $this->assertTrue($result2['payload']['transformed']);
    }

    public function testUpcastWithMissingVersionDefaultsToOne()
    {
        $chain = new EventUpcasterChain([new TestUpcasterV1ToV2()]);

        $eventData = [
            'message_uuid' => 'uuid-123',
            'message_name' => 'test.event',
            'created_at' => '2024-01-01T00:00:00.000000+00:00',
            'metadata' => [],
            'payload' => [],
        ];

        $result = $chain->upcast($eventData);

        $this->assertEquals(2, $result['metadata']['_aggregate_event_version']);
    }

    public function testGetUpcastersReturnsAllUpcasters()
    {
        $upcaster1 = new TestUpcasterV1ToV2();
        $upcaster2 = new TestUpcasterV2ToV3();
        $upcaster3 = new AnotherEventUpcaster();

        $chain = new EventUpcasterChain([$upcaster1, $upcaster2]);
        $chain->addUpcaster($upcaster3);

        $upcasters = $chain->getUpcasters();

        $this->assertCount(3, $upcasters);
        $this->assertSame($upcaster1, $upcasters[0]);
        $this->assertSame($upcaster2, $upcasters[1]);
        $this->assertSame($upcaster3, $upcasters[2]);
    }

    public function testUpcastPreservesNonPayloadAndMetadataFields()
    {
        $chain = new EventUpcasterChain([new TestUpcasterV1ToV2()]);

        $eventData = [
            'message_uuid' => 'uuid-123',
            'message_name' => 'test.event',
            'created_at' => '2024-01-01T12:34:56.789000+00:00',
            'metadata' => ['_aggregate_event_version' => 1, 'custom' => 'metadata'],
            'payload' => ['original' => 'data'],
        ];

        $result = $chain->upcast($eventData);

        $this->assertEquals('uuid-123', $result['message_uuid']);
        $this->assertEquals('test.event', $result['message_name']);
        $this->assertEquals('2024-01-01T12:34:56.789000+00:00', $result['created_at']);
        $this->assertEquals('metadata', $result['metadata']['custom']);
    }
}
