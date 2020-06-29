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

Feel free to use all the available methods of the existing [Http Client API](https://laravel.com/docs/7.x/http-client#introduction) when using this package, they will work just fine 😘.

``` php
use Plmrlnsnts\HttpExtended\Http;

Http::get('http://test.com', ['foo' => 'bar']);

// or

Http::withUrl('http://test.com')
    ->withQuery('foo', 'bar')
    ->execute('get');
```

Well for this example, the later version seems overkill. But for some cases, you will need to pass an **overwhelming number** of "query" or "body" parameters to a request. That's when things can get really nasty.

Here's what it would look like when using the `post` method when requesting to [Google My Business Location Insights api](https://developers.google.com/my-business/content/insight-data).

```php
use Plmrlnsnts\HttpExtended\Http;

$response = Http::withToken('google-access-token')
    ->post('https://business.googleapis.com/v4/accounts/{accountId}/locations:reportInsights', [
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

Now, here is another way of constructing the request using a fluent interface provided by this package.

```php
use Plmrlnsnts\HttpExtended\Http;

$response = Http::prepare()
    ->withToken('google-access-token')
    ->withUrl('https://business.googleapis.com/v4/accounts/{accountId}/locations:reportInsights')
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
