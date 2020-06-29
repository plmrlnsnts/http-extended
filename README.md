# Laravel Http Client - Extended

Minor improvements to the built-in http client library.

```php
use Plmrlnsnts\HttpExtended\Http;

$response = Http::prepare()
    ->withUrl('https://test.com')
    ->withBody('name', 'John Doe')
    ->withBody('email', 'john@example.com')
    ->execute('post');
```

## Installation

You can install the package via composer:

```bash
composer require plmrlnsnts/http-extended "^0.1"
```

## Usage

Feel free to use all the available methods on the existing [http client api](https://laravel.com/docs/7.x/http-client#introduction). Heck you can even **find-and-replace** all the occurence of `Illuminate\Support\Facades\Http` with `Plmrlnsnts\HttpExtended\Http`, and it will work just fine.

``` php
use Plmrlnsnts\HttpExtended\Http;

// Methods

Http::get('http://test.com', ['foo' => 'bar']);

Http::post('http://test.com', [
    'name' => 'John Doe',
    'email' => 'john@example.com',
]);

// Testing

Http::fake(fn () => ['fake' => 'response']);

Http::fakeSequence()
    ->push(['first' => 'response'])
    ->push(['second' => 'response'])
    ->whenEmpty(new Http::response());
```

In some cases, you will find yourself passing an **overwhelming number** of "query" or "body" parameters to a request. Here's an example of a `post()` request to [Google My Business Location Insights](https://developers.google.com/my-business/content/insight-data) api.

```php
use Plmrlnsnts\HttpExtended\Http;

$response = Http::post('{baseUrl}/locations:reportInsights', [
    'locationNames' => ['accounts/{accountId}/locations/locationId'],
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
            'startTime' => '2016-10-12T01:01:23Z',
            'endTime' => '2017-01-10T23:59:59Z',
        ]
    ]
]);
```

And this how you can *"fluently"* construct the same request using the package.

```php
use Plmrlnsnts\HttpExtended\Http;

$response = Http::prepare()
    ->withUrl('{baseUrl}/locations:reportInsights')
    ->withBody('locationNames', ['accounts/{accountId}/locations/locationId'])
    ->withBody('basicRequest.metricRequests.0.metric', 'QUERIES_DIRECT')
    ->withBody('basicRequest.metricRequests.1.metric', 'QUERIES_INDIRECT')
    ->withBody('basicRequest.timeRange.startTime', '2016-10-12T01:01:23Z')
    ->withBody('basicRequest.timeRange.endTime', '2017-01-10T23:59:59Z')
    ->execute('post');
```

### Making Requests

This package offers a variety of methods to contruct http requests:

#### prepare($wrapper = null)

Accepts an instance of a `wrapper` object (more about this later). You may also call this method without any arguments just for the sake of aligning the rest of the method chain.

```php
// 😥
$response = Http::withUrl('http://test.com')
    ->withQuery('foo', 'bar')
    ->execute('get');

// 🥰
$response = Http::prepare()
    ->withUrl('http://test.com')
    ->withQuery('foo', 'bar')
    ->execute('get');
```

Did I mention that it works with the other methods as well?

```php
$response = Http::prepare()
    ->withToken('yourAccessToken')
    ->post('http://test.com', ['foo' => 'bar']);
```

#### withQuery(string|array $key, $value = null)

Assigns a parameter to the "query". This method can accept a `key-value` pair or an `array`. It also supports "dot" notation.

```php
Http::withQuery('firstName', 'Johnny');
// ['firstName' => 'Johnny']

Http::withQuery(['lastName' => 'Depp']);
// ['lastName' => 'Depp']

Http::withQuery('skills.technical' => ['Pirate']);
// ['skills' => ['technical => ['Pirate']]]
```

This method can also be used along with `post|put|patch|delete` requests.

```php
Http::prepare()
    ->withUrl('http://test.com')
    ->withQuery('version', 'v2')
    ->withBody(['name' => 'Johnny Depp'])
    ->execute('post');

// POST http://test.com?version=v2
// BODY { "name" : "Johnny Depp" }
```

#### withBody(string|array $key, $value = null)

Assigns a parameter to the "request body". This method can accept a `key-value` pair or an `array`. It also supports "dot" notation.

```php
Http::withBody('firstName', 'Johnny');
// ['firstName' => 'Johnny']

Http::withBody(['lastName' => 'Depp']);
// ['lastName' => 'Depp']

Http::withBody('skills.technical' => ['Pirate']);
// ['skills' => ['technical => ['Pirate']]]
```

#### afterSending(callable $callback)

A method that accepts a `callable` to add a little bit of logic when a request has finished. Particulary useful when dealing with paginated result set.

```php
use Illuminate\Http\Client\Response;
use Plmrlnsnts\HttpExtended\Http;
use Plmrlnsnts\HttpExtended\PendingRequest;

$pendingRequest = Http::prepare()
    ->withUrl('http://test.com')
    ->withQuery('page', 1)
    ->withQuery('perPage', 100)
    ->afterSending(function (PendingRequest $request, Response $response) {
        $request->incrementQuery('page', 1);
        $request->canContinue = ! empty(data_get($response, 'data'));
    });

while ($pendingRequest->canContinue) {
    $response = $pendingRequest->execute('get');
}
```

### Wrapper objects

There could be a time when you have to perform a similar set-up on multiple requests, but you want to encapsulate this logic in one place. This is where wrapper objects (please help me think of a better name) can get in handy. Simply create a class with a `boot` method that accepts an instance of a `PendingRequest`, and you're good to go. Here's an example:

```php
use App\HttpClients;
use Plmrlnsnts\HttpExtended\PendingRequest;

class GoogleClient
{
    protected $googleAccount;

    public const REPORT_INSIGHTS_URL = '/accounts/{accountId}/locations:reportInsights';

    public function __construct($googleAccount)
    {
        $this->googleAccount = $googleAccount;
    }

    public function boot(PendingRequest $request)
    {
        if ($this->googleAccount->tokenExpired()) {
            $this->googleAccount->refreshToken();
        }

        $request->baseUrl('https://mybusiness.googleapis.com/v4');
        $request->withToken($this->account->token);
    }
}
```

You can now pass it to the `prepare` method when making http requests to the same api.

```php
$response = Http::prepare(new GoogleClient($account))
    ->withUrl(GoogleClient::REPORT_INSIGHTS_URL)
    ->withBody('locationNames', ['accounts/{accountId}/locations/locationId'])
    ->withBody('basicRequest.metricRequests.0.metric', 'QUERIES_DIRECT')
    ->withBody('basicRequest.metricRequests.1.metric', 'QUERIES_INDIRECT')
    ->withBody('basicRequest.timeRange.startTime', '2016-10-12T01:01:23Z')
    ->withBody('basicRequest.timeRange.endTime', '2017-01-10T23:59:59Z')
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
