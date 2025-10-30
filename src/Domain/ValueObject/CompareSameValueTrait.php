<?php

namespace Dranzd\Common\EventSourcing\Domain\ValueObject;

trait CompareSameValueTrait
{
    protected function checkSameValueAs($valueObject): bool
    {
        if (\get_class($this) !== \get_class($valueObject)) {
            return false;
        }

        if ($this instanceof ArrayValueObjectTranslatorCapable) {
            return $this->toArray() === $valueObject->toArray();
        }

        try {
            return $this->toNative() === $valueObject->toNative();
        } catch (Exception\ToNativeValueIncapable $e) {
            // do nothing
        }

        return false;
    }
}
