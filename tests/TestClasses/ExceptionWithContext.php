<?php

namespace Facade\FlareClient\Tests\TestClasses;

use Facade\FlareClient\Contracts\ProvidesFlareContext;

class ExceptionWithContext extends \Exception implements ProvidesFlareContext
{
    public function context(): array
    {
        return [
            'context' => [
                'another key' => 'another value',
            ],
        ];
    }
}
