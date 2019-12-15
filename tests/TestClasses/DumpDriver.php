<?php

namespace Facade\FlareClient\Tests\TestClasses;

use PHPUnit\Framework\Assert;
use Spatie\Snapshots\Drivers\YamlDriver;
use Symfony\Component\Yaml\Yaml;

class DumpDriver extends YamlDriver
{
    public function serialize($data): string
    {
        $yaml = parent::serialize($data);

        return $this->removeTimestamps($yaml);
    }

    public function match($expected, $actual)
    {
        if (is_array($actual)) {
            $actual = Yaml::dump($actual, PHP_INT_MAX);
        }

        $actual = $this->removeTimestamps($actual);

        Assert::assertEquals($expected, $actual);
    }

    protected function removeTimestamps($string)
    {
        return preg_replace('/sf-dump-([0-9]+)/m', 'sf-dump-000000', $string);
    }
}
