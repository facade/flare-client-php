<?php

namespace Facade\FlareClient;

use Exception;
use Throwable;
use Illuminate\Pipeline\Pipeline;
use Facade\FlareClient\Glows\Glow;
use Facade\FlareClient\Http\Client;
use Facade\FlareClient\Glows\Recorder;
use Facade\FlareClient\Concerns\HasContext;
use Facade\FlareClient\Enums\MessageLevels;
use Facade\FlareClient\Middleware\AddGlows;
use Illuminate\Contracts\Container\Container;
use Facade\FlareClient\Middleware\AnonymizeIp;
use Facade\FlareClient\Context\ContextContextDetector;
use Facade\FlareClient\Context\ContextDetectorInterface;

class Flare
{
    use HasContext;

    /** @var \Facade\FlareClient\Http\Client */
    private $client;

    /** @var \Facade\FlareClient\Api */
    private $api;

    /** @var array */
    private $middleware = [];

    /** @var \Facade\FlareClient\Glows\Recorder */
    private $recorder;

    /** @var string */
    private $applicationPath;

    /** @var \Illuminate\Contracts\Container\Container|null */
    private $container;

    /** @var ContextDetectorInterface */
    private $contextDetector;

    public static function register(string $apiKey, string $apiSecret = null, ContextDetectorInterface $contextDetector = null, Container $container = null)
    {
        $client = new Client($apiKey, $apiSecret);

        return new static($client, $contextDetector, $container);
    }

    public function __construct(Client $client, ContextDetectorInterface $contextDetector = null, Container $container = null, array $middleware = [])
    {
        $this->client = $client;
        $this->recorder = new Recorder();
        $this->contextDetector = $contextDetector ?? new ContextContextDetector();
        $this->container = $container;
        $this->middleware = $middleware;
        $this->api = new Api($this->client);

        $this->registerDefaultMiddleware();
    }

    public function getMiddleware(): array
    {
        return $this->middleware;
    }

    public function registerExceptionHandler()
    {
        set_exception_handler([$this, 'handleException']);

        return $this;
    }

    private function registerDefaultMiddleware()
    {
        return $this->registerMiddleware(new AddGlows($this->recorder));
    }

    public function registerMiddleware($callable)
    {
        $this->middleware[] = $callable;

        return $this;
    }

    public function getMiddlewares(): array
    {
        return $this->middleware;
    }

    public function glow(
        string $name,
        string $messageLevel = MessageLevels::INFO,
        array $metaData = []
    ) {
        $this->recorder->record(new Glow($name, $messageLevel, $metaData));
    }

    public function handleException(Throwable $throwable)
    {
        $this->report($throwable);
    }

    public function applicationPath(string $applicationPath)
    {
        $this->applicationPath = $applicationPath;

        return $this;
    }

    public function report(Throwable $throwable, callable $callback = null)
    {
        $report = $this->createReport($throwable);

        if (! is_null($callback)) {
            call_user_func($callback, $report);
        }

        $this->sendReportToApi($report);
    }

    public function sendTestReport(Throwable $throwable)
    {
        $this->api->sendTestReport($this->createReport($throwable));
    }

    private function sendReportToApi(Report $report)
    {
        try {
            $this->api->report($report);
        } catch (Exception $exception) {
        }
    }

    public function reset()
    {
        $this->api->sendQueuedReports();

        $this->userProvidedContext = [];
        $this->recorder->reset();
    }

    private function applyAdditionalParameters(Report $report)
    {
        $report
            ->stage($this->stage)
            ->messageLevel($this->messageLevel)
            ->setApplicationPath($this->applicationPath)
            ->userProvidedContext($this->userProvidedContext);
    }

    public function anonymizeIp()
    {
        $this->registerMiddleware(new AnonymizeIp);

        return $this;
    }

    public function createReport(Throwable $throwable): Report
    {
        $report = Report::createForThrowable(
            $throwable,
            $this->contextDetector->detectCurrentContext()
        );

        $this->applyAdditionalParameters($report);

        $report = (new Pipeline($this->container))
            ->send($report)
            ->through($this->middleware)
            ->then(function ($report) {
                return $report;
            });

        return $report;
    }
}
