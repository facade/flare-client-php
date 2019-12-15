<?php

namespace Facade\FlareClient\Tests\TestClasses;

use Facade\FlareClient\Tests\TestCase;
use PHPUnit\Framework\Assert;
use Spatie\Snapshots\Drivers\YamlDriver;
use Symfony\Component\Yaml\Yaml;

class CodeSnippetDriver extends YamlDriver
{
    public function serialize($data): string
    {
        $yaml = parent::serialize($data);

        return TestCase::makePathsRelative($yaml);
    }

    public function match($expected, $actual)
    {
        if (is_array($actual)) {
            $actual = Yaml::dump($actual, PHP_INT_MAX);
        }

        $actual = TestCase::makePathsRelative($actual);

        Assert::assertEquals($expected, $actual);
    }
}
