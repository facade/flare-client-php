<?php

namespace Facade\FlareClient\Tests\Context;

use Facade\FlareClient\Context\RequestContext;
use Facade\FlareClient\Tests\Concerns\MatchesCodeSnippetSnapshots;
use Facade\FlareClient\Tests\TestCase;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;

class RequestContextTest extends TestCase
{
    use MatchesCodeSnippetSnapshots;

    /** @test */
    public function it_can_return_the_context_as_an_array()
    {
        $get = ['get-key-1' => 'get-value-1'];

        $post = ['post-key-1' => 'post-value-1'];

        $request = [];

        $cookies = ['cookie-key-1' => 'cookie-value-1'];

        $files = [
            'file-one' => new UploadedFile(
                $this->getStubPath('file.txt'),
                'file-name.txt',
                'text/plain',
                UPLOAD_ERR_OK
            ),
            'file-two' => new UploadedFile(
                $this->getStubPath('file.txt'),
                'file-name.txt',
                'text/plain',
                UPLOAD_ERR_OK
            ),
        ];

        $server = [
            'HTTP_HOST' => 'example.com',
            'REMOTE_ADDR' => '1.2.3.4',
            'SERVER_PORT' => '80',
            'REQUEST_URI' => '/test',
        ];

        $content = 'my content';

        $request = new Request($get, $post, $request, $cookies, $files, $server, $content);

        $context = new RequestContext($request);

        $contextArray = $context->toArray();

        $this->assertMatchesCodeSnippetSnapshot($contextArray);
    }
}
