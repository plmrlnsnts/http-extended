# Laravel Http Client - Extended

Minor improvements to the built-in client library.

```php
use Plmrlnsnts\HttpExtended\Http;

$response = Http::prepare()
    ->withUrl('https://test.com')
    ->withQuery('api_key', 'api-key')
    ->withBody('user.email', 'john@example.com')
    ->execute('post');
```

## Installation

You can install the package via composer:

```bash
composer require plmrlnsnts/http-extended
```

## Usage

Feel free to use all the available methods of the existing [http client api](https://laravel.com/docs/7.x/http-client#introduction) when using this package, and it will work just fine. Heck you can even **find-and-replace** all the occurence of `Illuminate\Support\Facades\Http` with `Plmrlnsnts\HttpExtended\Http`.

``` php
use Plmrlnsnts\HttpExtended\Http;

Http::get('http://test.com', ['foo' => 'bar']);

// or

Http::prepare()
    ->withUrl('http://test.com')
    ->withQuery('foo', 'bar')
    ->execute('get');
```

Well for this example, the later version seems overkill 🙄. But for some cases, you will need to pass an **overwhelming number** of "query" or "body" parameters to a request. That's when things can get really nasty. Here's what it would look like when using the `post` method when requesting to [Google My Business Location Insights](https://developers.google.com/my-business/content/insight-data) api.

```php
use Plmrlnsnts\HttpExtended\Http;

$response = Http::withToken('<access-token>')
    ->post('<base-url>/locations:reportInsights', [
        'locationNames' => [
            'accounts/{accountId}/locations/locationId',
        ],
        'basicRequest' => [
            'metricRequests' => [
                [
                    'metric' => 'QUERIES_DIRECT',
                ],
                [
                    'metric' => 'QUERIES_INDIRECT',
                ],
            ],
            'timeRange' => [
                'startTime' => '2016-10-12T01:01:23.045123456Z',
                'endTime' => '2017-01-10T23:59:59.045123456Z',
            ]
        ]
    ]);
```

Now, here is another way of constructing the request *"fluently"*.

```php
use Plmrlnsnts\HttpExtended\Http;

$response = Http::prepare()
    ->withToken('<access-token>')
    ->withUrl('<base-url>/locations:reportInsights')
    ->withBody('locationNames', ['accounts/{accountId}/locations/locationId'])
    ->withBody('basicRequest.metricRequests.0.metric', 'QUERIES_DIRECT')
    ->withBody('basicRequest.metricRequests.1.metric', 'QUERIES_INDIRECT')
    ->withBody('basicRequest.timeRange.startTime', '2016-10-12T01:01:23.045123456Z')
    ->withBody('basicRequest.timeRange.endTime', '2017-01-10T23:59:59.045123456Z')
    ->execute('post');
```

### Testing

``` bash
composer test
```

## Security

If you discover any security related issues, please email paulmarlonsantos@gmail.com instead of using the issue tracker.

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
