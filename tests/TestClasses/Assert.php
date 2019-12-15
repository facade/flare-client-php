<?php

namespace Facade\FlareClient\Tests\TestClasses;

use PHPUnit\Framework\Assert as PHPUnit;
use PHPUnit\Framework\Constraint\ArraySubset;
use PHPUnit\Util\InvalidArgumentHelper;

class Assert extends PHPUnit
{
    public static function assertArraySubset($subset, $array, bool $checkForObjectIdentity = false, string $message = ''): void
    {
        if (! (is_array($subset) || $subset instanceof ArrayAccess)) {
            throw InvalidArgumentHelper::factory(1, 'array or ArrayAccess');
        }
        if (! (is_array($array) || $array instanceof ArrayAccess)) {
            throw InvalidArgumentHelper::factory(2, 'array or ArrayAccess');
        }
        $constraint = new ArraySubset($subset, $checkForObjectIdentity);

        static::assertThat($array, $constraint, $message);
    }
}
