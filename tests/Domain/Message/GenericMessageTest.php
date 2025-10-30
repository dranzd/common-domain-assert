<?php

namespace Dranzd\Common\EventSourcing\Tests\Domain\Message;

use Dranzd\Common\EventSourcing\Domain\Message\Command;
use Dranzd\Common\EventSourcing\Domain\Message\Event;
use Dranzd\Common\EventSourcing\Domain\Message\GenericMessage;
use Dranzd\Common\EventSourcing\Domain\Message\Message;
use Dranzd\Common\EventSourcing\Domain\Message\Query;
use PHPUnit\Framework\TestCase;

class TestCommand extends GenericMessage implements Command
{
    const MESSAGE_NAME = 'test.command';

    private $payload = [];

    public function withPayload(array $payload): static
    {
        $clone = clone $this;
        $clone->payload = $payload;
        return $clone;
    }

    public function getPayload(): array
    {
        return $this->payload;
    }

    public function getPayloadKeyValue(string $key)
    {
        if (! \array_key_exists($key, $this->payload)) {
            throw new \RuntimeException(
                \sprintf(
                    'Key %s not found on message payload of %s',
                    $key,
                    $this->getMessageName()
                )
            );
        }
        return $this->payload[$key];
    }

    public function getPayloadKeyValueOrDefault(string $key, $default)
    {
        if (! \array_key_exists($key, $this->payload)) {
            return $default;
        }
        return $this->payload[$key];
    }

    protected function setPayload(array $payload): void
    {
        $this->payload = $payload;
    }
}

class TestEvent extends GenericMessage implements Event
{
    const MESSAGE_NAME = 'test.event';

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

class TestQuery extends GenericMessage implements Query
{
    const MESSAGE_NAME = 'test.query';

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

class TestUnknownMessage extends GenericMessage
{
    const MESSAGE_NAME = 'test.unknown';

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

final class GenericMessageTest extends TestCase
{
    public function testConstructorSetsBasicProperties()
    {
        $uuid = 'test-uuid-123';
        $name = 'test.message';
        $payload = ['key' => 'value'];

        $message = new TestCommand($uuid, $name, $payload);

        $this->assertEquals($uuid, $message->getMessageUuid());
        $this->assertEquals($name, $message->getMessageName());
        $this->assertEquals($payload, $message->getPayload());
        $this->assertInstanceOf(\DateTimeImmutable::class, $message->getCreatedAt());
    }

    public function testCreatedAtIsInUTC()
    {
        $message = new TestCommand('uuid', 'name', []);
        
        $this->assertEquals('UTC', $message->getCreatedAt()->getTimezone()->getName());
    }

    public function testExpectedMessageNameWithConstant()
    {
        $this->assertEquals('test.command', TestCommand::expectedMessageName());
        $this->assertEquals('test.event', TestEvent::expectedMessageName());
        $this->assertEquals('test.query', TestQuery::expectedMessageName());
    }

    public function testFromArrayCreatesMessageCorrectly()
    {
        $createdAt = new \DateTimeImmutable('2024-01-01 12:00:00', new \DateTimeZone('UTC'));
        $array = [
            'message_uuid' => 'test-uuid',
            'message_name' => 'test.command',
            'created_at' => $createdAt,
            'metadata' => ['meta_key' => 'meta_value'],
            'payload' => ['payload_key' => 'payload_value'],
        ];

        $message = TestCommand::fromArray($array);

        $this->assertEquals('test-uuid', $message->getMessageUuid());
        $this->assertEquals('test.command', $message->getMessageName());
        $this->assertEquals($createdAt, $message->getCreatedAt());
        $this->assertEquals(['meta_key' => 'meta_value'], $message->getMetadata());
        $this->assertEquals(['payload_key' => 'payload_value'], $message->getPayload());
    }

    public function testFromArrayWithoutOptionalFields()
    {
        $createdAt = new \DateTimeImmutable();
        $array = [
            'message_uuid' => 'test-uuid',
            'message_name' => 'test.command',
            'created_at' => $createdAt,
        ];

        $message = TestCommand::fromArray($array);

        $this->assertEquals([], $message->getMetadata());
        $this->assertEquals([], $message->getPayload());
    }

    public function testToArraySerializesCorrectly()
    {
        $uuid = 'test-uuid';
        $name = 'test.command';
        $payload = ['key' => 'value'];
        $metadata = ['meta' => 'data'];

        $message = new TestCommand($uuid, $name, $payload);
        $message = $message->withMetadata($metadata);

        $array = $message->toArray();

        $this->assertEquals($uuid, $array['message_uuid']);
        $this->assertEquals($name, $array['message_name']);
        $this->assertIsString($array['created_at']);
        $this->assertEquals($metadata, $array['metadata']);
        $this->assertEquals($payload, $array['payload']);
    }

    public function testWithMessageUuidCreatesNewInstance()
    {
        $message = new TestCommand('original-uuid', 'name', []);
        $newMessage = $message->withMessageUuid('new-uuid');

        $this->assertEquals('original-uuid', $message->getMessageUuid());
        $this->assertEquals('new-uuid', $newMessage->getMessageUuid());
        $this->assertNotSame($message, $newMessage);
    }

    public function testWithMessageNameCreatesNewInstance()
    {
        $message = new TestCommand('uuid', 'original-name', []);
        $newMessage = $message->withMessageName('new-name');

        $this->assertEquals('original-name', $message->getMessageName());
        $this->assertEquals('new-name', $newMessage->getMessageName());
        $this->assertNotSame($message, $newMessage);
    }

    public function testWithMetadataReplacesMetadata()
    {
        $message = new TestCommand('uuid', 'name', []);
        $message = $message->withMetadata(['key1' => 'value1']);
        $newMessage = $message->withMetadata(['key2' => 'value2']);

        $this->assertEquals(['key1' => 'value1'], $message->getMetadata());
        $this->assertEquals(['key2' => 'value2'], $newMessage->getMetadata());
        $this->assertNotSame($message, $newMessage);
    }

    public function testMergeMetadataCombinesMetadata()
    {
        $message = new TestCommand('uuid', 'name', []);
        $message = $message->withMetadata(['key1' => 'value1']);
        $newMessage = $message->mergeMetadata(['key2' => 'value2']);

        $this->assertEquals(['key1' => 'value1'], $message->getMetadata());
        $this->assertEquals(['key1' => 'value1', 'key2' => 'value2'], $newMessage->getMetadata());
    }

    public function testWithPayloadCreatesNewInstance()
    {
        $message = new TestCommand('uuid', 'name', ['original' => 'payload']);
        $newMessage = $message->withPayload(['new' => 'payload']);

        $this->assertEquals(['original' => 'payload'], $message->getPayload());
        $this->assertEquals(['new' => 'payload'], $newMessage->getPayload());
        $this->assertNotSame($message, $newMessage);
    }

    public function testGetMetadataKeyValueReturnsValue()
    {
        $message = new TestCommand('uuid', 'name', []);
        $message = $message->withMetadata(['key' => 'value']);

        $this->assertEquals('value', $message->getMetadataKeyValue('key'));
    }

    public function testGetMetadataKeyValueThrowsExceptionForMissingKey()
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Key missing_key not found on message metadata');

        $message = new TestCommand('uuid', 'name', []);
        $message->getMetadataKeyValue('missing_key');
    }

    public function testGetMetadataKeyValueOrDefaultReturnsValue()
    {
        $message = new TestCommand('uuid', 'name', []);
        $message = $message->withMetadata(['key' => 'value']);

        $this->assertEquals('value', $message->getMetadataKeyValueOrDefault('key', 'default'));
    }

    public function testGetMetadataKeyValueOrDefaultReturnsDefault()
    {
        $message = new TestCommand('uuid', 'name', []);

        $this->assertEquals('default', $message->getMetadataKeyValueOrDefault('missing', 'default'));
    }

    public function testGetMetadataKeyValueNotNullOrDefaultReturnsValue()
    {
        $message = new TestCommand('uuid', 'name', []);
        $message = $message->withMetadata(['key' => 'value']);

        $this->assertEquals('value', $message->getMetadataKeyValueNotNullOrDefault('key', 'default'));
    }

    public function testGetMetadataKeyValueNotNullOrDefaultReturnsDefault()
    {
        $message = new TestCommand('uuid', 'name', []);

        $this->assertEquals('default', $message->getMetadataKeyValueNotNullOrDefault('missing', 'default'));
    }

    public function testHasMetadataKeyReturnsTrueForExistingKey()
    {
        $message = new TestCommand('uuid', 'name', []);
        $message = $message->withMetadata(['key' => 'value']);

        $this->assertTrue($message->hasMetadataKey('key'));
    }

    public function testHasMetadataKeyReturnsFalseForMissingKey()
    {
        $message = new TestCommand('uuid', 'name', []);

        $this->assertFalse($message->hasMetadataKey('missing'));
    }

    public function testIsMessageTypeCommandReturnsTrue()
    {
        $message = new TestCommand('uuid', 'name', []);

        $this->assertTrue($message->isMessageTypeCommand());
        $this->assertFalse($message->isMessageTypeEvent());
        $this->assertFalse($message->isMessageTypeQuery());
        $this->assertFalse($message->isMessageTypeUnknown());
        $this->assertEquals(Message::TYPE_COMMAND, $message->getMessageType());
    }

    public function testIsMessageTypeEventReturnsTrue()
    {
        $message = new TestEvent('uuid', 'name', []);

        $this->assertTrue($message->isMessageTypeEvent());
        $this->assertFalse($message->isMessageTypeCommand());
        $this->assertFalse($message->isMessageTypeQuery());
        $this->assertFalse($message->isMessageTypeUnknown());
        $this->assertEquals(Message::TYPE_EVENT, $message->getMessageType());
    }

    public function testIsMessageTypeQueryReturnsTrue()
    {
        $message = new TestQuery('uuid', 'name', []);

        $this->assertTrue($message->isMessageTypeQuery());
        $this->assertFalse($message->isMessageTypeCommand());
        $this->assertFalse($message->isMessageTypeEvent());
        $this->assertFalse($message->isMessageTypeUnknown());
        $this->assertEquals(Message::TYPE_QUERY, $message->getMessageType());
    }

    public function testIsMessageTypeUnknownReturnsTrue()
    {
        $message = new TestUnknownMessage('uuid', 'name', []);

        $this->assertTrue($message->isMessageTypeUnknown());
        $this->assertFalse($message->isMessageTypeCommand());
        $this->assertFalse($message->isMessageTypeEvent());
        $this->assertFalse($message->isMessageTypeQuery());
        $this->assertEquals(Message::TYPE_UNKNOWN, $message->getMessageType());
    }

    public function testPayloadContainerCapableTraitGetPayloadKeyValue()
    {
        $message = new TestCommand('uuid', 'name', ['key' => 'value']);

        $this->assertEquals('value', $message->getPayloadKeyValue('key'));
    }

    public function testPayloadContainerCapableTraitGetPayloadKeyValueThrowsException()
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Key missing not found on message payload');

        $message = new TestCommand('uuid', 'name', []);
        $message->getPayloadKeyValue('missing');
    }

    public function testPayloadContainerCapableTraitGetPayloadKeyValueOrDefault()
    {
        $message = new TestCommand('uuid', 'name', ['key' => 'value']);

        $this->assertEquals('value', $message->getPayloadKeyValueOrDefault('key', 'default'));
        $this->assertEquals('default', $message->getPayloadKeyValueOrDefault('missing', 'default'));
    }

    public function testMessageConstantsAreDefined()
    {
        $this->assertEquals('command', Message::TYPE_COMMAND);
        $this->assertEquals('event', Message::TYPE_EVENT);
        $this->assertEquals('query', Message::TYPE_QUERY);
        $this->assertEquals('unknown', Message::TYPE_UNKNOWN);
    }
}
