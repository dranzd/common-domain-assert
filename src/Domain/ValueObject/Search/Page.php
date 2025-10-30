<?php

namespace Dranzd\Common\EventSourcing\Domain\ValueObject\Search;

use Dranzd\Common\EventSourcing\Domain\Assert\Assertion;

class Page
{
    /**
     * @var integer
     */
    private $number;

    /**
     * @var integer
     */
    private $limit;

    public function __construct(int $number, int $limit)
    {
        Assertion::integer($number, 'Page number must be an integer');
        Assertion::integer($limit, 'Page limit must be an integer');

        Assertion::notEmpty($number, 'Page number must not be zero');

        $this->number = $number;
        $this->limit  = $limit;
    }

    public static function startDefault(): Page
    {
        return new self(1, 0);
    }

    public function startFrom(int $number): Page
    {
        Assertion::integer($number, 'Page number must be an integer');
        Assertion::notEmpty($number, 'Page number must not be zero');

        $clone = clone $this;

        $clone->number = $number;

        return $clone;
    }

    public function limitTo(int $limit): Page
    {
        Assertion::integer($limit, 'Page limit must be an integer');

        $clone = clone $this;

        $clone->limit = $limit;

        return $clone;
    }

    public function getStartingNumber(): int
    {
        return $this->number;
    }

    public function getLimit(): int
    {
        return $this->limit;
    }
}
