<?php

namespace Dranzd\Common\EventSourcing\Domain\ValueObject\Collection;

use Dranzd\Common\EventSourcing\Domain\ValueObject\ArrayValueObjectTranslatorCapable;
use Dranzd\Common\EventSourcing\Domain\ValueObject\CompareSameValueTrait;
use Dranzd\Common\EventSourcing\Domain\ValueObject\ValueObject;
use Doctrine\Common\Collections\ArrayCollection;

class Collection extends ArrayCollection implements ValueObject, ArrayValueObjectTranslatorCapable
{
    use CompareSameValueTrait;

    public function sameValueAs(ValueObject $object): bool
    {
        return $this->checkSameValueAs($object);
    }

    public function __toString(): string
    {
        return \sprintf('Object %s with object hash of %s', __METHOD__, \spl_object_hash($this));
    }

    public static function fromArray(array $array): Collection
    {
        return new Collection($array);
    }

    public function toArray(): array
    {
        return \array_map(
            function ($element) {
                if (!\is_object($element)) {
                    return $element;
                }
                if (\method_exists($element, 'toArray')) {
                    return $element->toArray();
                }
                if (\method_exists($element, 'toNative')) {
                    return $element->toNative();
                }
                return $element;
            },
            parent::toArray()
        );
    }

    public function hasValueObjectItem($anItem): bool
    {
        $filteredItems = $this->filter(
            function ($item) use ($anItem) {
                return $item instanceof ValueObject && $item->sameValueAs($anItem);
            }
        );

        return !$filteredItems->isEmpty();
    }
}
