<?php

namespace Dranzd\Common\EventSourcing\Domain\ValueObject\Search;

use Dranzd\Common\EventSourcing\Domain\Assert\Assertion;

class Query
{
    /**
     * @var Page
     */
    private $page;

    /**
     * @var array
     */
    private $filters;

    /**
     * @var array
     */
    private $sorters;

    public function __construct(
        Page $page = null,
        array $filters = [],
        array $sorters = []
    ) {
        if (null === $page) {
            $page = Page::startDefault()->startFrom(1)->limitTo(10);
        }

        $this->page    = $page;
        $this->filters = $filters;
        $this->sorters = $sorters;
    }

    public static function with(
        array $filters,
        array $sorters,
        int $pageNumber,
        int $itemsPerPage
    ): Query {
        Assertion::isArray($filters, 'Query for filters must be an array');
        Assertion::isArray($sorters, 'Query for sorters must be an array');

        $page = Page::startDefault()->startFrom($pageNumber)->limitTo($itemsPerPage);

        return new self($page, $filters, $sorters);
    }

    /**
     * @param  integer $pageNumber
     * @return Query
     */
    public function forPageNumber(int $pageNumber): Query
    {
        $clone = clone $this;

        $clone->page = $clone->page->startFrom($pageNumber);

        return $clone;
    }

    /**
     * @param  integer $itemsPerPage
     * @return Query
     */
    public function limitToItemsPerPage(int $itemsPerPage): Query
    {
        $clone = clone $this;

        $clone->page = $clone->page->limitTo($itemsPerPage);

        return $clone;
    }

    public function filterBy(array $filters): Query
    {
        Assertion::isArray($filters, 'Query for filters must be an array');

        $clone = clone $this;

        $clone->filters = $$filters;

        return $clone;
    }

    public function sortBy(array $sorters): Query
    {
        Assertion::isArray($sorters, 'Query for sorters must be an array');

        $clone = clone $this;

        $clone->sorters = $sorters;

        return $clone;
    }

    public function getPage(): Page
    {
        return $this->page;
    }

    public function getFilters(): array
    {
        return $this->filters;
    }

    public function getSorters(): array
    {
        return $this->sorters;
    }
}
