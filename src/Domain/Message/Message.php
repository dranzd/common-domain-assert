<?php

namespace Dranzd\Common\EventSourcing\Domain\Message;

interface Message
{
    public const TYPE_COMMAND = 'command';
    public const TYPE_EVENT = 'event';
    public const TYPE_QUERY = 'query';
    public const TYPE_UNKNOWN = 'unknown';

    public static function expectedMessageName(): string;

    public static function fromArray(array $array): Message;

    public function mergeMetadata(array $metadata): Message;

    public function withMetadata(array $metadata): Message;

    public function withPayload(array $payload): Message;

    public function toArray(): array;

    public function getMessageUuid(): string;

    public function getMessageType(): string;

    public function getMessageName(): string;

    public function getCreatedAt(): \DateTimeImmutable;

    public function getMetadataKeyValue(string $key);

    public function getMetadataKeyValueOrDefault(string $key, $default);

    public function getMetadataKeyValueNotNullOrDefault(string $key, $default);

    public function getMetadata(): array;

    public function getPayload(): array;

    public function isMessageTypeUnknown(): bool;

    public function isMessageTypeCommand(): bool;

    public function isMessageTypeEvent(): bool;

    public function isMessageTypeQuery(): bool;

    public function hasMetadataKey(string $key): bool;
}
