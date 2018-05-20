<?php

namespace Neat\Http\Test;

use Neat\Http\Input;
use Neat\Http\Request;
use Neat\Http\Upload;
use PHPUnit\Framework\TestCase;

class InputTest extends TestCase
{
    /**
     * Test empty input
     */
    public function testEmpty()
    {
        $request = new Request();
        $input   = new Input($request);

        $this->assertSame([], $input->all());
        $this->assertFalse($input->has('unknown'));
        $this->assertNull($input->get('unknown'));
        $this->assertSame([], $input->errors());
        $this->assertSame([], $input->errors('unknown'));
        $this->assertTrue($input->valid());
        $this->assertTrue($input->valid('unknown'));
    }

    /**
     * Test input from various source configurations
     */
    public function testFrom()
    {
        $request = new Request('POST', '/?var=query', ['var' => 'post']);
        $request = $request->withCookie('var', 'cookie');
        $request = $request->withFiles(['var' => [
            'tmp_name' => __DIR__ . '/test.txt',
            'name'     => 'file.txt',
            'size'     => 90996,
            'type'     => 'plain/text',
            'error'    => 0,
        ],]);

        $input   = new Input($request);

        $input->from('query');
        $this->assertSame(['var' => 'query'], $input->all());

        $input->from('post');
        $this->assertSame(['var' => 'post'], $input->all());

        $input->from('cookie');
        $this->assertSame(['var' => 'cookie'], $input->all());

        $input->from('files');
        $this->assertEquals([
            'var' => new Upload(__DIR__ . '/test.txt', 'file.txt', 'plain/text')
        ], $input->all());
    }

    /**
     * Test from empty sources
     */
    public function testFromEmpty()
    {
        $request = new Request();
        $input   = new Input($request);

        $this->expectExceptionObject(new \RuntimeException('Sources must not be empty'));

        $input->from();
    }

    /**
     * Test from unknown sources
     */
    public function testFromUnknown()
    {
        $request = new Request();
        $input   = new Input($request);

        $this->expectExceptionObject(new \RuntimeException('Unknown source: internet'));

        $input->from('query', 'internet');
    }

    /**
     * Test input filtering
     */
    public function testFilter()
    {
        $request = new Request('GET', '/?var=%20test%20');
        $input   = new Input($request);

        $this->assertSame(' test ', $input->get('var'));
        $this->assertSame('test', $input->filter('var', 'trim'));
        $this->assertSame('TEST', $input->filter('var', 'trim|strtoupper'));
        $this->assertSame('TEST', $input->filter('var', ['trim', 'strtoupper']));
        $this->assertNull($input->filter('unknown', 'trim'));
    }

    /**
     * Provide custom filter data
     *
     * @return array
     */
    public function provideCustomFilterData()
    {
        return [
            ['test', 'test', ['Not a number']],
            ['3', 3, ['Not an even number']],
            ['2', 2, []],
        ];
    }

    /**
     * Test custom filter
     *
     * @dataProvider provideCustomFilterData
     * @param string $value
     * @param mixed  $filtered
     * @param array  $errors
     */
    public function testCustomFilter($value, $filtered, $errors)
    {
        $even = function (&$value) {
            if (!is_numeric($value)) {
                return ['Not a number'];
            }

            $value = intval($value);
            if ($value % 2) {
                return ['Not an even number'];
            }

            return [];
        };

        $request = new Request('POST', '/', ['var' => $value]);
        $input   = new Input($request);
        $input->register('even', $even);

        $this->assertSame($filtered, $input->filter('var', 'even'));
        $this->assertSame($errors, $input->errors('var'));
        $this->assertSame(empty($errors), $input->valid('var'));
    }

    /**
     * Provide type data
     *
     * @return array
     */
    public function provideTypeData()
    {
        return [
            ['bool', null, null],
            ['bool', '', false],
            ['bool', '0', false],
            ['bool', '1', true],
            ['bool', '3.14', true],
            ['bool', 'any-other-string', true],
            ['int', null, null],
            ['int', '', 0],
            ['int', '0', 0],
            ['int', '1', 1],
            ['int', '3.14', 3],
            ['int', 'any-other-string', 0],
            ['float', null, null],
            ['float', '', 0.0],
            ['float', '0', 0.0],
            ['float', '1', 1.0],
            ['float', '3.14', 3.14],
            ['float', 'any-other-string', 0.0],
            ['string', null, null],
            ['string', '', ''],
            ['string', '0', '0'],
            ['string', '1', '1'],
            ['string', '3.14', '3.14'],
            ['string', 'any-other-string', 'any-other-string'],
        ];
    }

    /**
     * Test type casted input
     *
     * @param string $type
     * @param string $value
     * @param bool   $filtered
     * @dataProvider provideTypeData
     */
    public function testTypeCast($type, $value, $filtered)
    {
        $request = new Request('POST', '/', ['var' => $value]);
        $input   = new Input($request);

        $this->assertSame($filtered, $input->$type('var'));
    }

    /**
     * Test file input
     */
    public function testFile()
    {
        $request = new Request('POST', '/', ['bool' => true]);
        $request = $request->withFiles(['upload' => [
            'tmp_name' => __DIR__ . '/test.txt',
            'name'     => 'file.txt',
            'size'     => 90996,
            'type'     => 'plain/text',
            'error'    => 0,
        ],]);

        $input = new Input($request);

        $this->assertNull($input->file('bool'));
        $this->assertEquals(
            new Upload(__DIR__ . '/test.txt', 'file.txt', 'plain/text'),
            $input->file('upload')
        );
    }
}
