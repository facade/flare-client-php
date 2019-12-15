<?php

namespace Facade\FlareClient\Tests\Context;

use Facade\FlareClient\Context\ConsoleContext;
use Facade\FlareClient\Tests\TestCase;

class ConsoleContextTest extends TestCase
{
    /** @test */
    public function it_can_return_the_context_as_an_array()
    {
        $arguments = [
            'argument 1',
            'argument 2',
            'argument 3',
        ];

        $context = new ConsoleContext($arguments);

        $this->assertEquals(['arguments' => $arguments], $context->toArray());
    }
}
