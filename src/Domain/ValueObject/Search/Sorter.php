<?php

namespace Dranzd\Common\EventSourcing\Domain\ValueObject\Search;

interface Sorter extends Criteria
{
    /**
     * @return Sort
     */
    public static function inAscendingOrder(): Sorter;

    /**
     * @return Sort
     */
    public static function inDescendingOrder(): Sorter;

    /**
     * @return integer SORT_ASC|SORT_DESC
     */
    public function getOrder(): int;
}
