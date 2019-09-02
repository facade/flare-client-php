<?php

namespace Facade\FlareClient\Context;

use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;

class RequestContext implements ContextInterface
{
    /** @var \Symfony\Component\HttpFoundation\Request|null */
    protected $request;

    public function __construct(Request $request = null)
    {
        $this->request = $request ?? Request::createFromGlobals();
    }

    public function getRequest(): array
    {
        return [
            'url' => $this->request->getUri(),
            'ip' => $this->request->getClientIp(),
            'method' => $this->request->getMethod(),
            'useragent' => $this->request->headers->get('User-Agent'),
        ];
    }

    private function getFiles(): array
    {
        if (is_null($this->request->files)) {
            return [];
        }

        return array_map(function(UploadedFile $file) {

            return [
                'pathname' => $file->getPathname(),
                'size' => $file->getSize(),
                'mimeType' => $file->getMimeType()
            ];
        }, $this->request->files->all());
    }

    public function getSession(): array
    {
        $session = $this->request->getSession();

        return $session ? $session->all() : [];
    }

    public function getCookies(): array
    {
        return $this->request->cookies->all();
    }

    public function getHeaders(): array
    {
        return $this->request->headers->all();
    }

    public function getRequestData(): array
    {
        return [
            'queryString' => $this->request->query->all(),
            'body' => $this->request->request->all(),
            'files' => $this->getFiles(),
        ];
    }

    public function toArray(): array
    {
        return [
            'request' => $this->getRequest(),
            'request_data' => $this->getRequestData(),
            'headers' => $this->getHeaders(),
            'cookies' => $this->getCookies(),
            'session' => $this->getSession(),
        ];
    }
}
