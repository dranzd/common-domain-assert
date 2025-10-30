<?php

namespace Dranzd\Common\EventSourcing\Domain\ValueObject\Search;

interface Criteria
{
    public function sameValueAs(Criteria $criteria): bool;

    public function __toString(): string;

    public static function fromCodeAndValueOrNull(string $code, mixed $value): ?Criteria;

    public function isForFiltering(): bool;

    public function isForSorting(): bool;
}
