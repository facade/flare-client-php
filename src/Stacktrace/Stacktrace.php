<?php

namespace Facade\FlareClient\Stacktrace;

use Throwable;

class Stacktrace
{
    /** @var \Facade\FlareClient\Stacktrace\Frame[] */
    private $frames;

    public static function createForThrowable(Throwable $throwable): self
    {
        return new static($throwable->getTrace(), $throwable->getFile(), $throwable->getLine());
    }

    public function __construct(array $backtracke, string $topmostFile, string $topmostLine)
    {
        $currentFile = $topmostFile;
        $currentLine = $topmostLine;

        foreach ($backtracke as $rawFrame) {
            $this->frames[] = new Frame(
                $currentFile,
                $currentLine,
                $rawFrame['function'] ?? null,
                $rawFrame['class'] ?? null
            );

            $currentFile = $rawFrame['file'] ?? 'unknown';
            $currentLine = $rawFrame['line'] ?? 0;
        }

        $this->frames[] = new Frame(
            $currentFile,
            $currentLine,
            '[top]'
        );
    }

    public function firstFrame(): Frame
    {
        return $this->frames[0];
    }

    public function toArray(): array
    {
        return array_map(function (Frame $frame) {
            return $frame->toArray();
        }, $this->frames);
    }
}
