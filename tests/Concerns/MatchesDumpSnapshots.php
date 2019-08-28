<?php

namespace Facade\FlareClient\Tests\Concerns;

use Spatie\Snapshots\MatchesSnapshots;
use Facade\FlareClient\Tests\TestClasses\DumpDriver;

trait MatchesDumpSnapshots
{
    use MatchesSnapshots;

    public function assertMatchesDumpSnapshot(array $codeSnippet)
    {
        $this->assertMatchesSnapshot($codeSnippet, new DumpDriver());
    }
}
