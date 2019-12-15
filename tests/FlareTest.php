<?php

namespace Facade\FlareClient\Tests;

use Facade\FlareClient\Api;
use Facade\FlareClient\Enums\MessageLevels;
use Facade\FlareClient\Flare;
use Facade\FlareClient\Tests\Concerns\MatchesReportSnapshots;
use Facade\FlareClient\Tests\Mocks\FakeClient;
use Facade\FlareClient\Tests\TestClasses\ExceptionWithContext;
use PHPUnit\Framework\Exception;

class FlareTest extends TestCase
{
    use MatchesReportSnapshots;

    /** @var FakeClient */
    protected $fakeClient;

    /** @var Flare */
    protected $flare;

    public function setUp()
    {
        parent::setUp();

        Api::sendReportsInBatches(false);

        $this->fakeClient = new FakeClient();

        $this->flare = new Flare($this->fakeClient);

        $this->useTime('2019-01-01 12:34:56');
    }

    /* This function at the top so our snapshots won't fail
     * every time we add a test
     */
    protected function reportException()
    {
        $throwable = new Exception('This is a test');

        $this->flare->report($throwable);
    }

    /** @test */
    public function it_can_report_an_exception()
    {
        $this->reportException();

        $this->fakeClient->assertRequestsSent(1);

        $report = $this->fakeClient->getLastPayload();

        $this->assertMatchesReportSnapshot($report);
    }

    /** @test */
    public function it_can_reset_queued_exceptions()
    {
        Api::sendReportsInBatches(true);

        $this->reportException();

        $this->flare->reset();

        $this->fakeClient->assertRequestsSent(1);

        $this->flare->reset();

        $this->fakeClient->assertRequestsSent(1);
    }

    /** @test */
    public function it_can_add_user_provided_context()
    {
        $this->flare->context('my key', 'my value');

        $this->reportException();

        $this->fakeClient->assertLastRequestHas('context.context', [
            'my key' => 'my value',
        ]);
    }

    /** @test */
    public function callbacks_can_modify_the_report()
    {
        $this->flare->context('my key', 'my value');
        $this->flare->stage('production');
        $this->flare->messageLevel('info');

        $throwable = new Exception('This is a test');

        $this->flare->report($throwable, function ($report) {
            $report->context('my key', 'new value');
            $report->stage('development');
            $report->messageLevel('warning');
        });

        $this->fakeClient->assertLastRequestHas('context.context', [
            'my key' => 'new value',
        ]);
        $this->fakeClient->assertLastRequestHas('stage', 'development');
        $this->fakeClient->assertLastRequestHas('message_level', 'warning');
    }

    /** @test */
    public function it_can_anonymize_the_ip()
    {
        $_ENV['APP_RUNNING_IN_CONSOLE'] = true;
        $_SERVER['REMOTE_ADDR'] = '127.0.0.2';

        $this->reportException();

        $this->fakeClient->assertLastRequestContains('context.request', [
            'ip' => '127.0.0.2',
        ]);

        $this->flare->anonymizeIp();

        $this->reportException();

        $this->fakeClient->assertLastRequestContains('context.request', [
            'ip' => null,
        ]);
    }

    /** @test */
    public function it_can_merge_user_provided_context()
    {
        $this->flare->context('my key', 'my value');

        $this->flare->context('another key', 'another value');

        $this->reportException();

        $this->fakeClient->assertLastRequestHas('context.context', [
            'my key' => 'my value',
            'another key' => 'another value',
        ]);
    }

    /** @test */
    public function it_can_add_custom_exception_context()
    {
        $this->flare->context('my key', 'my value');

        $throwable = new ExceptionWithContext('This is a test');

        $this->flare->report($throwable);

        $this->fakeClient->assertLastRequestHas('context.context', [
            'my key' => 'my value',
            'another key' => 'another value',
        ]);
    }

    /** @test */
    public function it_can_add_a_group()
    {
        $this->flare->group('custom group', ['my key' => 'my value']);

        $this->reportException();

        $this->fakeClient->assertLastRequestHas('context.custom group', [
            'my key' => 'my value',
        ]);
    }

    /** @test */
    public function it_can_return_groups()
    {
        $this->flare->context('key', 'value');

        $this->flare->group('custom group', ['my key' => 'my value']);

        $this->assertSame(['key' => 'value'], $this->flare->getGroup());
        $this->assertSame([], $this->flare->getGroup('foo'));
        $this->assertSame(['my key' => 'my value'], $this->flare->getGroup('custom group'));
    }

    /** @test */
    public function it_can_merge_groups()
    {
        $this->flare->group('custom group', ['my key' => 'my value']);

        $this->flare->group('custom group', ['another key' => 'another value']);

        $this->reportException();

        $this->fakeClient->assertLastRequestHas('context.custom group', [
            'my key' => 'my value',
            'another key' => 'another value',
        ]);
    }

    /** @test */
    public function it_can_set_stages()
    {
        $this->flare->stage('production');

        $this->reportException();

        $this->fakeClient->assertLastRequestHas('stage', 'production');
    }

    /** @test */
    public function it_can_set_message_levels()
    {
        $this->flare->messageLevel('info');

        $this->reportException();

        $this->fakeClient->assertLastRequestHas('message_level', 'info');
    }

    /** @test */
    public function it_can_add_glows()
    {
        $this->flare->glow(
            'my glow',
            MessageLevels::INFO,
            ['my key' => 'my value']
        );

        $this->flare->glow(
            'another glow',
            MessageLevels::ERROR,
            ['another key' => 'another value']
        );

        $this->reportException();

        $payload = $this->fakeClient->getLastPayload();

        $glows = collect($payload['glows'])->map(function ($glow) {
            unset($glow['microtime']);

            return $glow;
        })->toArray();

        $this->assertEquals([
            [
                'name' => 'my glow',
                'message_level' => 'info',
                'meta_data' => ['my key' => 'my value'],
                'time' => 1546346096,
            ],
            [
                'name' => 'another glow',
                'message_level' => 'error',
                'meta_data' => ['another key' => 'another value'],
                'time' => 1546346096,
            ],
        ], $glows);
    }
}
