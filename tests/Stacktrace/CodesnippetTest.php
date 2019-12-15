<?php

namespace Facade\FlareClient\Tests\Stacktrace;

use Facade\FlareClient\Stacktrace\Codesnippet;
use Facade\FlareClient\Tests\TestCase;

class CodesnippetTest extends TestCase
{
    /** @test */
    public function it_can_get_a_snippet_in_the_middle_of_file()
    {
        $snippet = (new Codesnippet())
            ->surroundingLine(10)
            ->snippetLineCount(9)
            ->get($this->getTestFilePath('20-lines.txt'));

        $this->assertEquals([
            6 => 'Line 6',
            7 => 'Line 7',
            8 => 'Line 8',
            9 => 'Line 9',
            10 => 'Line 10',
            11 => 'Line 11',
            12 => 'Line 12',
            13 => 'Line 13',
            14 => 'Line 14',
        ], $snippet);
    }

    /** @test */
    public function it_can_get_the_beginning_of_a_file()
    {
        $snippet = (new Codesnippet())
            ->surroundingLine(1)
            ->snippetLineCount(2)
            ->get($this->getTestFilePath('20-lines.txt'));

        $this->assertEquals([
            1 => 'Line 1',
            2 => 'Line 2',
        ], $snippet);
    }

    /** @test */
    public function it_can_get_the_end_of_a_file()
    {
        $snippet = (new Codesnippet())
            ->surroundingLine(20)
            ->snippetLineCount(2)
            ->get($this->getTestFilePath('20-lines.txt'));

        $this->assertEquals([
            19 => 'Line 19',
            20 => 'Line 20',
        ], $snippet);
    }

    /** @test */
    public function it_will_get_the_ending_of_the_file_surrounding_lines_is_out_of_bounds()
    {
        $snippet = (new Codesnippet())
            ->surroundingLine(30)
            ->snippetLineCount(2)
            ->get($this->getTestFilePath('20-lines.txt'));

        $this->assertEquals([
            19 => 'Line 19',
            20 => 'Line 20',
        ], $snippet);
    }

    /** @test */
    public function it_will_get_the_entire_file_if_the_snippet_line_count_is_very_high()
    {
        $snippet = (new Codesnippet())
            ->surroundingLine(1)
            ->snippetLineCount(30)
            ->get($this->getTestFilePath('3-lines.txt'));

        $this->assertEquals([
            1 => 'Line 1',
            2 => 'Line 2',
            3 => 'Line 3',
        ], $snippet);
    }

    /** @test */
    public function it_will_return_an_empty_array_for_a_non_existing_file()
    {
        $snippet = (new Codesnippet())
            ->surroundingLine(1)
            ->snippetLineCount(30)
            ->get($this->getTestFilePath('this-file-does-not-exist'));

        $this->assertEquals([], $snippet);
    }

    /** @test */
    public function it_will_trim_all_spaces_at_the_end_of_a_line()
    {
        $snippet = (new Codesnippet())
            ->surroundingLine(2)
            ->snippetLineCount(3)
            ->get($this->getTestFilePath('lines-with-spaces.txt'));

        $this->assertEquals([
            1 => '   Line 1',
            2 => '   Line 2',
            3 => '   Line 3',
        ], $snippet);
    }

    /** @test */
    public function it_will_trim_long_lines()
    {
        $snippet = (new Codesnippet())
            ->surroundingLine(2)
            ->snippetLineCount(3)
            ->get($this->getTestFilePath('long-line.txt'));

        $this->assertCount(1, $snippet);
        $this->assertEquals(250, strlen($snippet[1]));
    }

    private function getTestFilePath(string $fileName): string
    {
        return __DIR__."/testFiles/{$fileName}";
    }
}
