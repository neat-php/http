<?php

namespace Neat\Http\Test;

use Neat\Http\Router;
use PHPUnit\Framework\TestCase;

class RouterTest extends TestCase
{
    public function testIn()
    {
        $router = new Router();
        $group  = $router->in('/test');
        $this->assertNotSame($router, $group);
    }

    private function router(): Router
    {
        $router = new Router();
        $router->get('/test', 'test');
        $router->get('/test/$id:\d+', 'test-id-number');
        $router->get('/test/$id:\w+', 'test-id-word');
        $router->get('/test/test', 'get-test-test');
        $router->post('/test/test', 'post-test-test');
        $router->put('/test/test', 'put-test-test');
        $router->patch('/test/test', 'patch-test-test');
        $router->delete('/test/test', 'delete-test-test');
        $router->get('/arg/*', 'test-arg');
        $router->controller('/any', 'any-test');

        return $router;
    }

    public function testAll()
    {
        $router = $this->router();

        $this->assertSame('test', $router->match('GET', '/test'));
        $this->assertSame('test-id-number', $router->match('GET', '/test/5', $parameters));
        $this->assertSame(['id' => '5'], $parameters);
        $this->assertSame('test-id-word', $router->match('GET', '/test/hello', $parameters));
        $this->assertSame(['id' => 'hello'], $parameters);
        $this->assertSame('get-test-test', $router->match('GET', '/test/test'));
        $this->assertSame('post-test-test', $router->match('POST', '/test/test'));
        $this->assertSame('put-test-test', $router->match('PUT', '/test/test'));
        $this->assertSame('patch-test-test', $router->match('PATCH', '/test/test'));
        $this->assertSame('delete-test-test', $router->match('DELETE', '/test/test'));
        $this->assertSame('test-arg', $router->match('GET', '/arg/bla/5', $parameters));
        $this->assertSame(['bla', '5'], $parameters);
        $this->assertSame('test-arg', $router->match('GET', '/arg/bla/5/and/more', $parameters));
        $this->assertSame(['bla', '5', 'and', 'more'], $parameters);
        $this->assertSame('any-test', $router->match('GET', '/any'));
        $this->assertSame('any-test', $router->match('POST', '/any'));
    }

    public function provideExceptionData()
    {
        return [
            [404, 'GET', '/hello-world'],
            [405, 'POST', '/test'],
            [404, 'GET', '/test/hello-world'],
        ];
    }

    /**
     * @dataProvider provideExceptionData
     * @param int    $code
     * @param string $method
     * @param string $path
     */
    public function testExceptions(int $code, string $method, string $path)
    {
        $this->expectExceptionCode($code);
        $this->router()->match($method, $path);
    }
}
