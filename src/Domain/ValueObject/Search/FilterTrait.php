<?php

namespace Dranzd\Common\EventSourcing\Domain\ValueObject\Search;

trait FilterTrait
{
    public function isForFiltering(): bool
    {
        return true;
    }

    public function isForSorting(): bool
    {
        return false;
    }

    public function sameClassAs(Object $object): bool
    {
        return get_class($this) === get_class($object);
    }
}
