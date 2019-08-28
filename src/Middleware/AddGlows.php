<?php

namespace Facade\FlareClient\Middleware;

use Facade\FlareClient\Report;
use Facade\FlareClient\Glows\Recorder;

class AddGlows
{
    /** @var Recorder */
    private $recorder;

    public function __construct(Recorder $recorder)
    {
        $this->recorder = $recorder;
    }

    public function handle(Report $report, $next)
    {
        foreach ($this->recorder->glows() as $glow) {
            $report->addGlow($glow);
        }

        return $next($report);
    }
}
