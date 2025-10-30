<?php

namespace Dranzd\Common\EventSourcing\Tests;

use Dranzd\Common\EventSourcing\EventSourcedAggregate;
use PHPUnit\Framework\TestCase;

class DummyAggregate extends EventSourcedAggregate
{
    protected function handleEvent($event): void
    {
    }
}

final class ExampleTest extends TestCase
{
    public function testApplyAndRelease()
    {
        $agg = new DummyAggregate();
        $agg->apply(new \stdClass());
        $this->assertCount(1, $agg->releaseEvents());
    }
}
