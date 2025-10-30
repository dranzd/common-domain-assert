<?php

namespace Dranzd\Common\EventSourcing\Domain\Message;

use Dranzd\Common\EventSourcing\Domain\ValueObject\Identity\Uuid;

abstract class GenericMessage implements Message
{
    const SERIALIZE_DATE_FORMAT = 'Y-m-d\TH:i:s.uP';

    /**
     * @var string
     */
    private $messageUuid;

    /**
     * @var string
     */
    private $messageName;

    /**
     * @var \DateTimeImmutable
     */
    private $createdAt;

    /**
     * @var array
     */
    private $metadata = [];

    public function __construct(
        string $messageUuid,
        string $messageName,
        array  $payload
    ) {
        $this->messageUuid = $messageUuid;
        $this->messageName = $messageName;

        $this->init();
        $this->setPayload($payload);
    }

    /**
     * Initialize the minimum required data for a generic message
     *
     * Allows a child to initialize the required properties with common method of
     * generating the values.
     *
     * @return void
     */
    protected function init(): void
    {
        if ($this->messageUuid === null) {
            $this->messageUuid = Uuid::generateAsString();
        }

        if ($this->messageName === null) {
            $this->messageName = \get_class($this);
        }

        if ($this->createdAt === null) {
            $this->createdAt = new \DateTimeImmutable();
            $this->createdAt = $this->createdAt->setTimezone(new \DateTimeZone('UTC'));
        }
    }

    public static function fromArray(array $array): static
    {
        $reflectionClass = new \ReflectionClass(\get_called_class());

        /** @var GenericMessage $message */
        $message = $reflectionClass->newInstanceWithoutConstructor();

        $message->messageUuid = $array['message_uuid'];
        $message->messageName = $array['message_name'];
        $message->createdAt   = $array['created_at'];

        $message->setMetadata($array['metadata'] ?? []);
        $message->setPayload($array['payload'] ?? []);

        return $message;
    }

    public static function expectedMessageName(): string
    {
        return \defined(static::class . '::MESSAGE_NAME')
            ? \constant(static::class . '::MESSAGE_NAME')
            : static::class
        ;
    }

    final public function withMessageUuid(string $messageUuid): static
    {
        $clone = clone $this;

        $clone->messageUuid = $messageUuid;

        return $clone;
    }

    final public function withMessageName(string $messageName): static
    {
        $clone = clone $this;

        $clone->messageName = $messageName;

        return $clone;
    }

    public function mergeMetadata(array $metdata): static
    {
        return $this->withMetadata(\array_merge_recursive($this->getMetadata(), $metdata));
    }

    public function withMetadata(array $metadata): static
    {
        $clone = clone $this;

        $clone->metadata = $metadata;

        return $clone;
    }

    public function withPayload(array $payload): static
    {
        return $this;
    }

    public function toArray(): array
    {
        $createdAt = $this->createdAt
            ? $this->createdAt->format(self::SERIALIZE_DATE_FORMAT)
            : '';

        return [
            'message_uuid' => $this->messageUuid,
            'message_name' => $this->messageName,
            'created_at'   => $createdAt,
            'metadata'     => $this->getMetadata(),
            'payload'      => $this->getPayload(),
        ];
    }

    public function getMessageUuid(): string
    {
        return $this->messageUuid;
    }

    public function getMessageType(): string
    {
        if ($this->isMessageTypeCommand()) {
            return Message::TYPE_COMMAND;
        }
        if ($this->isMessageTypeEvent()) {
            return Message::TYPE_EVENT;
        }
        if ($this->isMessageTypeQuery()) {
            return Message::TYPE_QUERY;
        }
        return Message::TYPE_UNKNOWN;
    }

    public function getMessageName(): string
    {
        return $this->messageName;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getMetadataKeyValue(string $key)
    {
        if (!$this->hasMetadataKey($key)) {
            throw new \RuntimeException(
                \sprintf(
                    'Key %s not found on message metadata of %s',
                    $key,
                    $this->messageName
                )
            );
        }

        return $this->metadata[$key];
    }

    public function getMetadataKeyValueOrDefault(string $key, $default)
    {
        return $this->hasMetadataKey($key) ? $this->metadata[$key] : $default;
    }

    public function getMetadataKeyValueNotNullOrDefault(string $key, $default)
    {
        return $this->metadata[$key] ?? $default;
    }

    public function getMetadata(): array
    {
        return $this->metadata;
    }

    public function getPayload(): array
    {
        return [];
    }

    public function isMessageTypeUnknown(): bool
    {
        return !$this->isMessageTypeCommand()
            && !$this->isMessageTypeEvent()
            && !$this->isMessageTypeQuery();
    }

    public function isMessageTypeCommand(): bool
    {
        return $this instanceof Command;
    }

    public function isMessageTypeEvent(): bool
    {
        return $this instanceof Event;
    }

    public function isMessageTypeQuery(): bool
    {
        return $this instanceof Query;
    }

    public function hasMetadataKey(string $key): bool
    {
        return \array_key_exists($key, $this->metadata);
    }

    protected function setMetadata(array $metadata): void
    {
        $this->metadata = $metadata;
    }

    /**
     * Default method to set payload.
     *
     * This does not suggest that the child class is limited to using $payload as
     * a means to store the data.  This only assumes by default that the child
     * class is using this method.
     *
     * @see PayloadContainerCapableTrait One method of handling payload
     *
     * @param array $payload
     *
     * @return void
     */
    protected function setPayload(array $payload): void
    {
        if (!\property_exists($this, 'payload')) {
            return;
        }

        $this->{'payload'} = $payload;
    }
}
