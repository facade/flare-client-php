<?php

namespace Facade\FlareClient\Tests\Concerns;

use Spatie\Snapshots\MatchesSnapshots;
use Facade\FlareClient\Tests\TestClasses\ReportDriver;

trait MatchesReportSnapshots
{
    use MatchesSnapshots;

    public function assertMatchesReportSnapshot(array $report)
    {
        $this->assertMatchesSnapshot($report, new ReportDriver());
    }
}
