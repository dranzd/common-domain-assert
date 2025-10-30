<?php

namespace Dranzd\Common\EventSourcing\Domain\ValueObject\Exception;

final class InvalidNativeArgument extends \InvalidArgumentException
{
    final public function __construct($value, array $allowedTypes)
    {
        $this->message = sprintf(
            'Argument "%s" is invalid. Allowed types for argument are "%s".',
            $value,
            implode(', ', $allowedTypes)
        );
    }
}
