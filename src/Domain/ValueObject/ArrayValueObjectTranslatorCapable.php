<?php

namespace Dranzd\Common\EventSourcing\Domain\ValueObject;

interface ArrayValueObjectTranslatorCapable
{
    public static function fromArray(array $array): ValueObject;

    public function toArray(): array;
}
