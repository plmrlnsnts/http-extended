<?php

namespace Plmrlnsnts\HttpExtended;

use Illuminate\Support\Facades\Facade;

/**
 * @method static \GuzzleHttp\Promise\PromiseInterface response($body = null, $status = 200, $headers = [])
 * @method static \Illuminate\Http\Client\Factory fake($callback = null)
 * @method static \Illuminate\Http\Client\Response delete(string $url, array $data = [])
 * @method static \Illuminate\Http\Client\Response execute(string $method)
 * @method static \Illuminate\Http\Client\Response get(string $url, array $query = [])
 * @method static \Illuminate\Http\Client\Response head(string $url, array $query = [])
 * @method static \Illuminate\Http\Client\Response patch(string $url, array $data = [])
 * @method static \Illuminate\Http\Client\Response post(string $url, array $data = [])
 * @method static \Illuminate\Http\Client\Response put(string $url, array $data = [])
 * @method static \Illuminate\Http\Client\Response send(string $method, string $url, array $options = [])
 * @method static \Illuminate\Http\Client\ResponseSequence fakeSequence(string $urlPattern = '*')
 * @method static \Plmrlnsnts\HttpExtended\PendingRequest accept(string $contentType)
 * @method static \Plmrlnsnts\HttpExtended\PendingRequest acceptJson()
 * @method static \Plmrlnsnts\HttpExtended\PendingRequest afterSending(callable $callback)
 * @method static \Plmrlnsnts\HttpExtended\PendingRequest asForm()
 * @method static \Plmrlnsnts\HttpExtended\PendingRequest asJson()
 * @method static \Plmrlnsnts\HttpExtended\PendingRequest asMultipart()
 * @method static \Plmrlnsnts\HttpExtended\PendingRequest attach(string $name, string $contents, string|null $filename = null, array $headers = [])
 * @method static \Plmrlnsnts\HttpExtended\PendingRequest beforeSending(callable $callback)
 * @method static \Plmrlnsnts\HttpExtended\PendingRequest bodyFormat(string $format)
 * @method static \Plmrlnsnts\HttpExtended\PendingRequest contentType(string $contentType)
 * @method static \Plmrlnsnts\HttpExtended\PendingRequest prepare($wrapper = null)
 * @method static \Plmrlnsnts\HttpExtended\PendingRequest retry(int $times, int $sleep = 0)
 * @method static \Plmrlnsnts\HttpExtended\PendingRequest stub(callable $callback)
 * @method static \Plmrlnsnts\HttpExtended\PendingRequest timeout(int $seconds)
 * @method static \Plmrlnsnts\HttpExtended\PendingRequest withBasicAuth(string $username, string $password)
 * @method static \Plmrlnsnts\HttpExtended\PendingRequest withBody(array|string $key, $value = null)
 * @method static \Plmrlnsnts\HttpExtended\PendingRequest withCookies(array $cookies, string $domain)
 * @method static \Plmrlnsnts\HttpExtended\PendingRequest withDigestAuth(string $username, string $password)
 * @method static \Plmrlnsnts\HttpExtended\PendingRequest withHeaders(array $headers)
 * @method static \Plmrlnsnts\HttpExtended\PendingRequest withOptions(array $options)
 * @method static \Plmrlnsnts\HttpExtended\PendingRequest withoutRedirecting()
 * @method static \Plmrlnsnts\HttpExtended\PendingRequest withoutVerifying()
 * @method static \Plmrlnsnts\HttpExtended\PendingRequest withQuery(array|string $key, $value = null)
 * @method static \Plmrlnsnts\HttpExtended\PendingRequest withToken(string $token, string $type = 'Bearer')
 * @method static \Plmrlnsnts\HttpExtended\PendingRequest withUrl(string $url)
 * @method static \Plmrlnsnts\HttpExtended\PendingRequest withWrapper($wrapper)
 *
 * @see \Plmrlnsnts\HttpExtended\Factory
 */
class Http extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return Factory::class;
    }
}
