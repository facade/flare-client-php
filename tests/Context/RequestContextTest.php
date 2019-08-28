<?php

namespace Facade\FlareClient\Tests\Context;

use Facade\FlareClient\Context\RequestContext;
use Facade\FlareClient\Tests\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Facade\FlareClient\Tests\Concerns\MatchesCodeSnippetSnapshots;

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
            ['file-one' =>
                [
                    'name' => 'file-name.txt',
                    'type' => 'text/plain',
                    'tmp_name' => $this->getStubPath('file.txt'),
                    'error' => UPLOAD_ERR_OK,
                    'size' => 123,
                ],
            ],
            ['file-two' =>
                [
                    'name' => 'file-name.txt',
                    'type' => 'text/plain',
                    'tmp_name' => $this->getStubPath('file.txt'),
                    'error' => UPLOAD_ERR_OK,
                    'size' => 123,
                ],
            ],
        ];

        $server = [
            'HTTP_HOST' => 'example.com',
            'REMOTE_ADDR' => '1.2.3.4',
            'SERVER_PORT' => '80',
            'REQUEST_URI' => '/test'
        ];

        $content = 'my content';

        $request = new Request($get, $post, $request, $cookies, $files, $server, $content);

        $context = new RequestContext($request);

        $contextArray = $context->toArray();

        $this->assertMatchesCodeSnippetSnapshot($contextArray);
    }
}
