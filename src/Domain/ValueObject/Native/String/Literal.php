<?php

namespace Dranzd\Common\EventSourcing\Domain\ValueObject\Native\String;

use Dranzd\Common\EventSourcing\Domain\ValueObject\CompareSameValueTrait;
use Dranzd\Common\EventSourcing\Domain\ValueObject\ScalarValueObjectTranslatorCapable;
use Dranzd\Common\EventSourcing\Domain\ValueObject\ValueObject;

class Literal implements ValueObject, ScalarValueObjectTranslatorCapable
{
    use CompareSameValueTrait;

    /**
     * @var string
     */
    protected $value;

    public function __construct(string $value = '')
    {
        $this->value = $value;
    }

    public function sameValueAs(ValueObject $object): bool
    {
        return $this->checkSameValueAs($object);
    }

    public function __toString()
    {
        return \sprintf('Literal value object of %s', $this->value);
    }

    public static function fromNative(): Literal
    {
        $value = func_get_arg(0);

        return new static($value);
    }

    public function toNative()
    {
        return $this->value;
    }

    public function isEmpty()
    {
        return \is_string($this->value) && '' === $this->value;
    }
}
