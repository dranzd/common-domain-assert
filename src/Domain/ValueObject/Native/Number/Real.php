<?php

namespace Dranzd\Common\EventSourcing\Domain\ValueObject\Native\Number;

use Dranzd\Common\EventSourcing\Domain\ValueObject\CompareSameValueTrait;
use Dranzd\Common\EventSourcing\Domain\ValueObject\ScalarValueObjectTranslatorCapable;
use Dranzd\Common\EventSourcing\Domain\ValueObject\ValueObject;

class Real implements ValueObject, ScalarValueObjectTranslatorCapable
{
    use CompareSameValueTrait;

    /**
     * @var float
     */
    protected $value;

    public function __construct(float $value = 0.0)
    {
        $this->value = $value;
    }

    public function sameValueAs(ValueObject $object): bool
    {
        return $this->checkSameValueAs($object);
    }

    public function __toString()
    {
        return \sprintf('Real value object of %s', $this->value);
    }

    public static function fromNative(): Real
    {
        $value = func_get_arg(0);

        return new static($value);
    }

    public function toNative()
    {
        return $this->value;
    }

    public static function withNoValue()
    {
        return new static(0.0);
    }

    public function isEmpty(): bool
    {
        return 0 === $this->value;
    }

    public function isNegative(): bool
    {
        return $this->value < 0;
    }

    public function plus(Real $reak): Real
    {
        return self::fromNative($this->value + $reak->value);
    }

    public function minus(Real $reak): Real
    {
        return self::fromNative($this->value - $reak->value);
    }

    public function multiply(Real $reak): Real
    {
        return self::fromNative($this->toNative() * $reak->toNative());
    }

    public function isGreaterThan(Real $reak): bool
    {
        return $this->toNative() > $reak->toNative();
    }

    public function isLessThan(Real $reak): bool
    {
        return $this->toNative() < $reak->toNative();
    }

    public function isGreaterThanOrEqualTo(Real $reak): bool
    {
        return $this->toNative() >= $reak->toNative();
    }

    public function isLessThanOrEqualTo(Real $reak): bool
    {
        return $this->toNative() <= $reak->toNative();
    }

    public function isEqualTo(Real $reak): bool
    {
        return $this->toNative() === $reak->toNative();
    }

    public function isNotEqualTo(Real $reak): bool
    {
        return $this->toNative() !== $reak->toNative();
    }
}
