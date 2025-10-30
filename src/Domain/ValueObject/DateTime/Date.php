<?php

namespace Dranzd\Common\EventSourcing\Domain\ValueObject\DateTime;

use Dranzd\Common\EventSourcing\Domain\Assert\Assertion;
use Dranzd\Common\EventSourcing\Domain\ValueObject\Native\String\Literal;

class Date extends Literal
{
    private const DATE_FORMAT = 'Y-m-d';

    public function __construct($value = null)
    {
        if (null === $value) {
            $value = \date(self::DATE_FORMAT);
        }

        Assertion::string($value);
        self::isFormatValid($value);

        $this->value = $value;
    }

    final public static function isFormatValid($datetime): bool
    {
        return false !== \DateTime::createFromFormat(self::DATE_FORMAT, $datetime);
    }
}
