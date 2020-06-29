<?php

namespace Plmrlnsnts\HttpExtended\Tests;

use Illuminate\Http\Client\Request;
use Illuminate\Http\Client\Response;
use Orchestra\Testbench\TestCase;
use Plmrlnsnts\HttpExtended\Http;
use Plmrlnsnts\HttpExtended\PendingRequest;
use RuntimeException;

class HttpExtendedTest extends TestCase
{
    /** @test */
    public function it_can_accept_a_wrapper_object()
    {
        tap(Http::prepare(), function ($request) {
            $this->assertInstanceOf(PendingRequest::class, $request);
        });

        tap(Http::prepare(WrapperExample::class), function ($request) {
            $this->assertInstanceOf(WrapperExample::class, $request->getWrapper());
        });

        tap(Http::prepare(new WrapperExample), function ($request) {
            $this->assertInstanceOf(WrapperExample::class, $request->getWrapper());
        });
    }

    /** @test */
    public function it_should_throw_an_error_when_a_wrapper_object_has_no_boot_method()
    {
        $this->expectException(RuntimeException::class);

        Http::prepare(new class {});
    }

    /** @test */
    public function it_can_assign_a_url()
    {
        Http::fake();

        Http::withUrl('http://foo')->execute('get');

        Http::assertSent(function (Request $request) {
            return $request->url() === 'http://foo';
        });
    }

    /** @test */
    public function it_can_assign_a_query()
    {
        $request = Http::withQuery('firstName', 'John');

        $this->assertEquals('John', $request->getQuery('firstName'));

        $request->withQuery(['lastName' => 'Doe']);

        $this->assertEquals('Doe', $request->getQuery('lastName'));

        $request->withQuery('skills.technical', ['Laravel']);

        $this->assertEquals(['Laravel'], $request->getQuery('skills.technical'));
    }

    /** @test */
    public function it_can_assign_a_body()
    {
        $request = Http::withBody('firstName', 'John');

        $this->assertEquals('John', $request->getBody('firstName'));

        $request->withBody(['lastName' => 'Doe']);

        $this->assertEquals('Doe', $request->getBody('lastName'));

        $request->withBody('skills.technical', ['Laravel']);

        $this->assertEquals(['Laravel'], $request->getBody('skills.technical'));
    }

    /** @test */
    public function it_can_increment_a_query()
    {
        $pendingRequest = Http::withQuery('page', 1);

        $this->assertEquals(1, $pendingRequest->getQuery('page'));

        $pendingRequest->incrementQuery('page', 1);

        $this->assertEquals(2, $pendingRequest->getQuery('page'));
    }

    /** @test */
    public function it_can_increment_a_body()
    {
        $pendingRequest = Http::withBody('page', 1);

        $this->assertEquals(1, $pendingRequest->getBody('page'));

        $pendingRequest->incrementBody('page', 1);

        $this->assertEquals(2, $pendingRequest->getBody('page'));
    }

    /** @test */
    public function it_preserves_the_query_when_requests_are_neither_get_nor_head()
    {
        Http::fake();

        Http::prepare()
            ->withUrl('http://foo')
            ->withQuery('apiKey', 'api-key')
            ->withBody('accountId', 'account-id')
            ->execute('post');

        Http::assertSent(function (Request $request) {
            return $request->url() === 'http://foo?apiKey=api-key'
                && $request['accountId'] === 'account-id';
        });
    }

    /** @test */
    public function it_can_accept_an_after_sending_callback()
    {
        Http::fake();

        Http::afterSending(function ($request, $response) {
            $this->assertInstanceOf(PendingRequest::class, $request);
            $this->assertInstanceOf(Response::class, $response);
        })->execute('get');
    }
}

class WrapperExample
{
    public function boot(PendingRequest $request) {
        //
    }
}
