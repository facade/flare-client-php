<?php

namespace Facade\FlareClient\Tests\Stacktrace;

use Facade\FlareClient\Stacktrace\Stacktrace;
use Facade\FlareClient\Tests\Concerns\MatchesCodeSnippetSnapshots;
use Facade\FlareClient\Tests\TestCase;

class StrackTraceTest extends TestCase
{
    use MatchesCodeSnippetSnapshots;

    /** @test */
    public function it_can_convert_an_exception_to_an_array()
    {
        $exception = (new ThrowAndReturnExceptionAction())->execute();

        $stackTrace = Stacktrace::createForThrowable($exception)->toArray();

        $this->assertTrue(is_array($stackTrace));

        $this->assertGreaterThan(1, count($stackTrace));

        $this->assertMatchesCodeSnippetSnapshot($stackTrace[0]);
    }

    /** @test */
    public function it_can_create_a_stacktrace()
    {
        $stackTrace = Stacktrace::create()->toArray();

        $this->assertTrue(is_array($stackTrace));

        $this->assertGreaterThan(1, count($stackTrace));

        $this->assertMatchesCodeSnippetSnapshot($stackTrace[0]);
    }

    /** @test */
    public function it_can_detect_application_frames()
    {
        $applicationPath = '/Users/marcel/Code/flare-testapp';

        $backtrace = json_decode(file_get_contents(__DIR__.'/testFiles/backtrace.json'), true);

        $stackTrace = new Stacktrace($backtrace, $applicationPath);

        $this->assertSame(8, $stackTrace->firstApplicationFrameIndex());
    }
}
