<?php

namespace Facade\FlareClient\Tests\Concerns;

use Facade\FlareClient\Tests\TestClasses\DumpDriver;
use Spatie\Snapshots\MatchesSnapshots;

trait MatchesDumpSnapshots
{
    use MatchesSnapshots;

    public function assertMatchesDumpSnapshot(array $codeSnippet)
    {
        $this->assertMatchesSnapshot($codeSnippet, new DumpDriver());
    }
}
