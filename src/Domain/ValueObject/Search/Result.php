<?php

namespace Dranzd\Common\EventSourcing\Domain\ValueObject\Search;

use Dranzd\Common\EventSourcing\Domain\Assert\Assertion;

class Result
{
    /**
     * @var array
     */
    private $items;

    /**
     * @var integer
     */
    private $total;

    public function __construct(array $items, int $total)
    {
        Assertion::isArray($items, 'Search result items must be an array');
        Assertion::integer($total, 'Search result total must be an integer');

        $this->items = $items;
        $this->total = $total;
    }

    public function getItems(): array
    {
        return $this->items;
    }

    public function getTotal(): int
    {
        return $this->total;
    }

    public function toArray(): array
    {
        $items = array_map(
            function ($item) {
                if (is_object($item)) {
                    if (method_exists($item, 'toArray')) {
                        return $item->toArray();
                    }
                    if (method_exists($item, 'toNative')) {
                        return $item->toNative();
                    }
                }
                return $item;
            },
            $this->items
        );

        return [
            'items' => $items,
            'total' => $this->total,
        ];
    }
}
