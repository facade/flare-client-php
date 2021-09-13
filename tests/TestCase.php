<?php

namespace Facade\FlareClient\Tests;

use Facade\FlareClient\Glows\Glow;
use Facade\FlareClient\Report;
use Facade\FlareClient\Tests\TestClasses\FakeTime;
use PHPUnit\Framework\TestCase as BaseTestCase;

class TestCase extends BaseTestCase
{
    public function setUp()
    {
        parent::setUp();

        Report::$fakeTrackingUuid = 'fake-uuid';
    }

    public static function makePathsRelative(string $text): string
    {
        return str_replace(dirname(__DIR__, 1), '', $text);
    }

    public function useTime(string $dateTime, string $format = 'Y-m-d H:i:s')
    {
        $fakeTime = new FakeTime($dateTime, $format);

        Report::useTime($fakeTime);
        Glow::useTime($fakeTime);
    }

    public function getStubPath(string $stubName): string
    {
        return __DIR__."/stubs/{$stubName}";
    }
}
