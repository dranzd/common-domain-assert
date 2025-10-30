<?php

namespace Dranzd\Common\EventSourcing\Domain\ValueObject;

trait ToStringUsingObjectHashTrait
{
    public function __toString(): string
    {
        return \sprintf('Object %s with object hash of %s', __METHOD__, \spl_object_hash($this));
    }
}
