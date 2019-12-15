<?php

namespace Facade\FlareClient\Tests\Stacktrace;

use Facade\FlareClient\Stacktrace\File;
use Facade\FlareClient\Tests\TestCase;

class FileTest extends TestCase
{
    /** @var \Facade\FlareClient\Stacktrace\File */
    protected $file;

    public function setUp()
    {
        $this->file = new File($this->getTestFilePath('20-lines.txt'));
    }

    /** @test */
    public function it_can_get_the_number_of_lines()
    {
        $this->assertEquals(20, $this->file->numberOfLines());
    }

    /** @test */
    public function it_can_get_a_specific_line()
    {
        $line = $this->file->getLine(18);

        $this->assertEquals($line, 'Line 18'.PHP_EOL);
    }

    /** @test */
    public function it_can_get_the_next_line()
    {
        $this->file->getLine(18);

        $this->assertEquals($this->file->getNextLine(), 'Line 19'.PHP_EOL);
    }

    /** @test */
    public function it_will_get_the_next_line_if_no_line_number_is_given()
    {
        $this->file->getLine(18);

        $this->assertEquals($this->file->getLine(), 'Line 19'.PHP_EOL);
    }

    /** @test */
    public function it_will_return_an_empty_string_for_a_line_that_doesnt_exist()
    {
        $this->assertEquals('', $this->file->getLine(21));

        $this->assertEquals('', $this->file->getNextLine());
    }

    private function getTestFilePath(string $fileName): string
    {
        return __DIR__."/testFiles/{$fileName}";
    }
}
