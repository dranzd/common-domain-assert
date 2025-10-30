<?php

namespace Dranzd\Common\EventSourcing\Domain\ValueObject\Custom\Quantity;

use Dranzd\Common\EventSourcing\Domain\ValueObject\ArrayValueObjectTranslatorCapable;
use Dranzd\Common\EventSourcing\Domain\ValueObject\CompareSameValueTrait;
use Dranzd\Common\EventSourcing\Domain\ValueObject\Native\Number\Real as Value;
use Dranzd\Common\EventSourcing\Domain\ValueObject\Native\String\Literal as Unit;
use Dranzd\Common\EventSourcing\Domain\ValueObject\ToStringUsingObjectHashTrait;
use Dranzd\Common\EventSourcing\Domain\ValueObject\ValueObject;
use Dranzd\Common\EventSourcing\Util\ArrayUtil;

class Basic implements ValueObject, ArrayValueObjectTranslatorCapable
{
    use CompareSameValueTrait, ToStringUsingObjectHashTrait;

    /**
     * @var Value
     */
    protected $value;

    /**
     * @var Unit
     */
    protected $unit;

    protected function __construct(Value $value, Unit $unit)
    {
        $this->value = $value;
        $this->unit = $unit;
    }

    public static function ofNoValue(): Basic
    {
        return static::fromArray([0, '']);
    }

    public static function fromArray(array $array): Basic
    {
        if (isset($array[0])) {
            return \call_user_func_array(['static', 'fromNative'], $array);
        }

        \extract(ArrayUtil::keyToCamelCase($array));

        return static::fromNative($value, $unit);
    }

    public function sameValueAs(ValueObject $object): bool
    {
        return $this->checkSameValueAs($object);
    }

    public function toArray(): array
    {
        return [
            'value' => $this->value->toNative(),
            'unit' => $this->unit->toNative(),
        ];
    }

    protected static function fromNative(): Basic
    {
        $value = (int) \func_get_arg(0);
        $unit = (string) \func_get_arg(1);

        $value = Value::fromNative($value);
        $unit = Unit::fromNative($unit);

        return new static($value, $unit);
    }
}
