<?php

namespace Dranzd\Common\EventSourcing\Domain\ValueObject\Search;

trait CriteriaProviderTrait
{
    /**
     * @var array
     */
    private $filters;

    /**
     * @var array
     */
    private $sorters;

    final public function filterFromCodeAndValueOrNull(string $code, mixed $value): ?Filter
    {
        foreach ($this->filters as $filter) {
            $filter = $filter::fromCodeAndValueOrNull($code, $value);
            if ($filter) {
                return $filter;
            }
        }

        return null;
    }

    final public function sorterFromCodeAndValueOrNull(string $code, mixed $value): ?Sorter
    {
        foreach ($this->sorters as $sorter) {
            $sorter = $sorter::fromCodeAndValueOrNull($code, $value);
            if ($sorter) {
                return $sorter;
            }
        }

        return null;
    }
}
