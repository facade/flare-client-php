<?php

namespace Facade\FlareClient\Tests\Truncation;

use Facade\FlareClient\Truncation\ReportTrimmer;
use Facade\FlareClient\Truncation\TrimStringsStrategy;
use PHPUnit\Framework\TestCase;

class TrimStringsStrategyTest extends TestCase
{
    /** @test */
    public function it_can_trim_long_strings_in_payload()
    {
        foreach (TrimStringsStrategy::thresholds() as $threshold) {
            [$payload, $expected] = $this->createLargePayload($threshold);

            $strategy = new TrimStringsStrategy(new ReportTrimmer());
            $this->assertSame($expected, $strategy->execute($payload));
        }
    }

    /** @test */
    public function it_does_not_trim_short_payloads()
    {
        $payload = [
            'data' => [
                'body' => 'short',
                'nested' => [
                    'message' => 'short',
                ],
            ],
        ];

        $strategy = new TrimStringsStrategy(new ReportTrimmer());

        $trimmedPayload = $strategy->execute($payload);

        $this->assertSame($payload, $trimmedPayload);
    }

    protected function createLargePayload($threshold)
    {
        $payload = $expected = [
            'data' => [
                'messages' => [],
            ],
        ];

        while (strlen(json_encode($payload)) < ReportTrimmer::getMaxPayloadSize()) {
            $payload['data']['messages'][] = str_repeat('A', $threshold + 10);
            $expected['data']['messages'][] = str_repeat('A', $threshold);
        }

        return [$payload, $expected];
    }
}
