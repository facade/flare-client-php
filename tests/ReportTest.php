<?php

namespace Facade\FlareClient\Tests;

use Exception;
use Facade\FlareClient\Context\ConsoleContext;
use Facade\FlareClient\Glows\Glow;
use Facade\FlareClient\Report;
use Facade\FlareClient\Tests\Concerns\MatchesReportSnapshots;
use Facade\FlareClient\Tests\TestClasses\FakeTime;

class ReportTest extends TestCase
{
    use MatchesReportSnapshots;

    public function setUp()
    {
        parent::setUp();

        Report::useTime(new FakeTime('2019-01-01 01:23:45'));
    }

    /** @test */
    public function it_can_create_a_report()
    {
        $report = Report::createForThrowable(new Exception('this is an exception'), new ConsoleContext());

        $report = $report->toArray();

        $this->assertMatchesReportSnapshot($report);
    }

    /** @test */
    public function it_can_create_a_report_for_a_string_message()
    {
        $report = Report::createForMessage('this is a message', 'Log', new ConsoleContext());

        $report = $report->toArray();

        $this->assertMatchesReportSnapshot($report);
    }

    /** @test */
    public function it_can_create_a_report_with_glows()
    {
        /** @var Report $report */
        $report = Report::createForThrowable(new Exception('this is an exception'), new ConsoleContext());

        $report->addGlow(new Glow('Glow 1', 'info', ['meta' => 'data']));

        $report = $report->toArray();

        $this->assertMatchesReportSnapshot($report);
    }

    /** @test */
    public function it_can_create_a_report_with_meta_data()
    {
        /** @var Report $report */
        $report = Report::createForThrowable(new Exception('this is an exception'), new ConsoleContext());

        $metadata = [
            'some' => 'data',
            'something' => 'more',
        ];

        $report->userProvidedContext(['meta' => $metadata]);

        $this->assertEquals($metadata, $report->toArray()['context']['meta']);
    }

    /** @test */
    public function it_will_generate_a_uuid()
    {
        $report = Report::createForThrowable(new Exception('this is an exception'), new ConsoleContext());

        $this->assertIsString($report->trackingUuid());

        $this->assertIsString($report->toArray()['tracking_uuid']);
    }
}
