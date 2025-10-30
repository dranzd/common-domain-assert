<?php

namespace Dranzd\Common\EventSourcing\Domain\ValueObject\Search;

trait SorterTrait
{
    public static function inAscendingOrder(): Sorter
    {
        return new static(SORT_ASC);
    }

    public static function inDescendingOrder(): Sorter
    {
        return new static(SORT_DESC);
    }

    /**
     * @param  string $code
     * @param  string $value
     * @return null|Sort
     */
    public static function fromCodeAndValueOrNull(string $code, mixed $value): ?Sorter
    {
        if (!\defined(static::class . '::FILTER_CODE')) {
            return null;
        }
        if ($code !== \constant(static::class . '::FILTER_CODE')) {
            return null;
        }
        if (SORT_ASC === $value) {
            return static::inAscendingOrder();
        }
        if (SORT_DESC === $value) {
            return static::inDescendingOrder();
        }
        return null;
    }

    public function sameValueAs(Criteria $object): bool
    {
        return $this->sameClassAs($object)
            && $this->order === $object->order
        ;
    }

    public function isForFiltering(): bool
    {
        return false;
    }

    public function isForSorting(): bool
    {
        return true;
    }

    public function sameClassAs(Object $object): bool
    {
        return get_class($this) === get_class($object);
    }

    public function getOrder(): int
    {
        return $this->order;
    }
}
