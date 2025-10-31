<?php

namespace Dranzd\Common\EventSourcing\Domain\Message;

trait PayloadContainerCapableTrait
{
    /**
     * @var array
     */
    private $payload = [];

    public function withPayload(array $payload): static
    {
        $clone = clone $this;

        $clone->payload = $payload;

        return $clone;
    }

    public function getPayloadKeyValue(string $key)
    {
        if (! \array_key_exists($key, $this->payload)) {
            throw new \RuntimeException(
                \sprintf(
                    'Key %s not found on message payload of %s',
                    $key,
                    $this->messageName
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

    public function getPayload(): array
    {
        return $this->payload;
    }

    protected function setPayload(array $payload): void
    {
        $this->payload = $payload;
    }
}
