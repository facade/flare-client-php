<?php

namespace Facade\FlareClient\Tests\Truncation;

use Facade\FlareClient\Truncation\ReportTrimmer;
use Facade\FlareClient\Truncation\TrimContextItemsStrategy;
use Illuminate\Support\Str;
use PHPUnit\Framework\TestCase;

class TrimContextItemsStrategyTest extends TestCase
{
    protected function setUp()
    {
        parent::setUp();

        ReportTrimmer::setMaxPayloadSize(52428);
    }

    /** @test */
    public function it_can_trim_long_context_items_in_payload()
    {
        foreach (TrimContextItemsStrategy::thresholds() as $threshold) {
            [$payload, $expected] = $this->createLargePayload($threshold);

            $strategy = new TrimContextItemsStrategy(new ReportTrimmer());
            $this->assertSame($expected, $strategy->execute($payload));
        }
    }

    /** @test */
    public function it_does_not_trim_short_payloads()
    {
        $payload = [
            'context' => [
                'queries' => [
                    1, 2, 3, 4,
                ],
            ],
        ];

        $strategy = new TrimContextItemsStrategy(new ReportTrimmer());

        $trimmedPayload = $strategy->execute($payload);

        $this->assertSame($payload, $trimmedPayload);
    }

    protected function createLargePayload($threshold)
    {
        $payload = $expected = [
            'context' => [
                'queries' => [],
            ],
        ];

        $contextKeys = [];

        while (strlen(json_encode($payload)) < ReportTrimmer::getMaxPayloadSize()) {
            $payloadItems = range(0, $threshold + 10);

            $contextKeys[] = $contextKey = Str::random();

            $payload['context'][$contextKey][] = $payloadItems;
            $expected['context'][$contextKey][] = array_slice($payloadItems, $threshold * -1, $threshold);
        }

        foreach ($contextKeys as $contextKey) {
            $expected['context'][$contextKey] = array_slice($expected['context'][$contextKey], $threshold * -1, $threshold);
        }

        return [$payload, $expected];
    }
}
