<?php

namespace Dranzd\Common\EventSourcing\Domain\EventSourcing;

trait AggregateEventWithPrivateConstructorTrait
{
    private function __construct()
    {
        parent::__construct();

        // nothing here
    }
}
