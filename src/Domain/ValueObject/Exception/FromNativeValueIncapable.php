<?php

namespace Dranzd\Common\EventSourcing\Domain\ValueObject\Exception;

use Dranzd\Common\EventSourcing\Domain\ValueObject\ValueObject;

final class FromNativeValueIncapable extends \DomainException
{
    final public static function fromObject(ValueObject $object): FromNativeValueIncapable
    {
        return new static(
            sprintf(
                'Object of instance %s is incapable creating instance from native value.',
                get_class($object)
            )
        );
    }
}
