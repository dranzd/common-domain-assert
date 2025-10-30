<?php

namespace Dranzd\Common\EventSourcing\Domain\ValueObject\Native\Number;

use Dranzd\Common\EventSourcing\Domain\ValueObject\CompareSameValueTrait;
use Dranzd\Common\EventSourcing\Domain\ValueObject\ScalarValueObjectTranslatorCapable;
use Dranzd\Common\EventSourcing\Domain\ValueObject\ValueObject;

class Integer implements ValueObject, ScalarValueObjectTranslatorCapable
{
    use CompareSameValueTrait;

    /**
     * @var int
     */
    protected $value;

    public function __construct(int $value = 0)
    {
        $this->value = $value;
    }

    public function sameValueAs(ValueObject $object): bool
    {
        return $this->checkSameValueAs($object);
    }

    public function __toString()
    {
        return \sprintf('Integer value object of %s', $this->value);
    }

    public static function fromNative(): Integer
    {
        $value = func_get_arg(0);

        return new static($value);
    }

    public function toNative()
    {
        return $this->value;
    }

    public function isEmpty(): bool
    {
        return 0 === $this->value;
    }

    public function isNegative(): bool
    {
        return $this->value < 0;
    }

    public function plus(Integer $number): Integer
    {
        return self::fromNative($this->value + $number->value);
    }

    public function minus(Integer $number): Integer
    {
        return self::fromNative($this->value - $number->value);
    }

    public function multiply(Integer $number): Integer
    {
        return self::fromNative($this->toNative() * $number->toNative());
    }

    public function isGreaterThan(Integer $number): bool
    {
        return $this->toNative() > $number->toNative();
    }

    public function isLessThan(Integer $number): bool
    {
        return $this->toNative() < $number->toNative();
    }

    public function isGreaterThanOrEqualTo(Integer $number): bool
    {
        return $this->toNative() >= $number->toNative();
    }

    public function isLessThanOrEqualTo(Integer $number): bool
    {
        return $this->toNative() <= $number->toNative();
    }

    public function isEqualTo(Integer $number): bool
    {
        return $this->toNative() === $number->toNative();
    }

    public function isNotEqualTo(Integer $number): bool
    {
        return $this->toNative() !== $number->toNative();
    }
}
