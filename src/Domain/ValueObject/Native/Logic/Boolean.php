<?php

namespace Dranzd\Common\EventSourcing\Domain\ValueObject\Native\Logic;

use Dranzd\Common\EventSourcing\Domain\ValueObject\CompareSameValueTrait;
use Dranzd\Common\EventSourcing\Domain\ValueObject\ScalarValueObjectTranslatorCapable;
use Dranzd\Common\EventSourcing\Domain\ValueObject\ValueObject;

class Boolean implements ValueObject, ScalarValueObjectTranslatorCapable
{
    use CompareSameValueTrait;

    /**
     * @var bool
     */
    protected $value;

    public function __construct(bool $value)
    {
        $this->value = $value;
    }

    public static function asTrue(): Boolean
    {
        return new static(true);
    }

    public static function asFalse(): Boolean
    {
        return new static(false);
    }

    public function sameValueAs(ValueObject $object): bool
    {
        return $this->checkSameValueAs($object);
    }

    public function __toString()
    {
        return \sprintf('Boolean value object of %s', \strval($this->value));
    }

    public static function fromNative(): Boolean
    {
        $value = func_get_arg(0);

        return new static($value);
    }

    public function toNative()
    {
        return $this->value;
    }

    public function isTrue(): bool
    {
        return $this->toNative() === true;
    }

    public function isFalse(): bool
    {
        return $this->toNative() === false;
    }

    public function toggle(): Boolean
    {
        return new static(!$this->isTrue());
    }
}
