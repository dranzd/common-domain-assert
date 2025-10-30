<?php

namespace Dranzd\Common\EventSourcing\Domain\ValueObject\Money;

use Dranzd\Common\EventSourcing\Domain\ValueObject\ArrayValueObjectTranslatorCapable;
use Dranzd\Common\EventSourcing\Domain\ValueObject\CompareSameValueTrait;
use Dranzd\Common\EventSourcing\Domain\ValueObject\Native\Number\Integer as Amount;
use Dranzd\Common\EventSourcing\Domain\ValueObject\Native\String\Literal as Currency;
use Dranzd\Common\EventSourcing\Domain\ValueObject\ToStringUsingObjectHashTrait;
use Dranzd\Common\EventSourcing\Domain\ValueObject\ValueObject;
use Dranzd\Common\EventSourcing\Util\ArrayUtil;

class Basic implements ValueObject, ArrayValueObjectTranslatorCapable
{
    use CompareSameValueTrait, ToStringUsingObjectHashTrait;

    /**
     * @var Amount
     */
    protected $amount;

    /**
     * @var Currency
     */
    protected $currency;

    protected function __construct(Amount $amount, Currency $currency)
    {
        $this->amount = $amount;
        $this->currency = $currency;
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

        return static::fromNative($amount, $currency);
    }

    public function sameValueAs(ValueObject $object): bool
    {
        return $this->checkSameValueAs($object);
    }

    public function toArray(): array
    {
        return [
            'amount' => $this->amount->toNative(),
            'currency' => $this->currency->toNative(),
        ];
    }

    protected static function fromNative(): Basic
    {
        $amount = (int) \func_get_arg(0);
        $currency = (string) \func_get_arg(1);

        $amount = Amount::fromNative($amount);
        $currency = Currency::fromNative($currency);

        return new static($amount, $currency);
    }
}
