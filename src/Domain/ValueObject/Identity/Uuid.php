<?php

namespace Dranzd\Common\EventSourcing\Domain\ValueObject\Identity;

use Dranzd\Common\EventSourcing\Domain\Assert\Assertion;
use Dranzd\Common\EventSourcing\Domain\ValueObject\Native\String\Literal;
use Ramsey\Uuid\Uuid as BaseUuid;

class Uuid extends Literal
{
    public function __construct($value = null)
    {
        if (null === $value) {
            $value = BaseUuid::uuid4()->toString();
        }

        Assertion::string($value);
        Assertion::uuid($value);

        $this->value = $value;
    }

    public static function fromNative(): Uuid
    {
        $value = \func_get_arg(0);

        return new static($value);
    }

    public static function generateAsString(): string
    {
        return (new static())->toNative();
    }
}
