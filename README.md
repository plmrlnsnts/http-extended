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
composer require plmrlnsnts/http-extended
```

## Usage

Feel free to use all the available methods on the existing [http client api](https://laravel.com/docs/7.x/http-client#introduction). Heck you can even **find-and-replace** all the occurence of `Illuminate\Support\Facades\Http` with `Plmrlnsnts\HttpExtended\Http`, and it will work just fine.

``` php
use Plmrlnsnts\HttpExtended\Http;

Http::get('http://test.com', ['foo' => 'bar']);

Http::post('http://test.com', [
    'name' => 'John Doe',
    'email' => 'john@example.com',
]);

Http::fake(fn () => ['fake' => 'response']);

Http::fakeSequence()
    ->push(['first' => 'response'])
    ->push(['second' => 'response'])
    ->whenEmpty(new Http::response())
```

In some cases, you will find yourself passing an **overwhelming number** of "query" or "body" parameters to a request. Here's an example of a `post()` request to [Google My Business Location Insights](https://developers.google.com/my-business/content/insight-data) api.

```php
use Plmrlnsnts\HttpExtended\Http;

$response = Http::post('{baseUrl}/locations:reportInsights', [
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

Did I mention that it works well too with the existing methods?

```php
$response = Http::prepare()
    ->withToken('yourAccessToken')
    ->post('http://test.com', ['foo' => 'bar']);
```

#### withQuery(string|array $key, $value = null)

Assigns a parameter to the query. This method can accept a `key-value` pair or an `array`. It also supports "dot" notation.

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

Assigns a parameter to the request body. This method supports "dot" notation.

```php
Http::prepare()
    ->withUrl('http://test.com')
    ->withBody('firstName', 'Johnny') // ['firstName' => 'Johnny']
    ->withBody(['lastName' => 'Depp']) // ['lastName' => 'Depp']
    ->withBody('skills.technical' => ['Pirate']) // ['skills' => ['technical => ['Pirate']]]
    ->execute('post');
```

#### afterSending(callable $callback)

A method that accepts a `function` to add a little bit of logic when a request has finished. Particulary useful when dealing with paginated result set.

```php
use Illuminate\Http\Client\Response;
use Plmrlnsnts\HttpExtended\Http;
use Plmrlnsnts\HttpExtended\PendingRequest;

$pendingRequest = Http::prepare()
    ->withUrl('http://test.com')
    ->withQuery('page', 1)
    ->withQuery('perPage', 100)
    ->afterSending(function (PendingRequest $request, Response $response) {
        $rows = data_get($response, 'data', []);
        $request->canContinue = ! empty($rows);
        $request->incrementQuery('page', 1);
    });

while ($pendingRequest->canContinue) {
    $response = $pendingRequest->execute('get');
}
```

### Wrapper objects

When you are dealing with a number of http requests from the same third-party api provider, you should be able to notice that you are doing the same set-up in multiple places. You can reach out to wrapper objects for this particular scenario.

```php
// code 1
$account = auth()->user();
Http::prepare()
    ->withUrl("https://test.com/api/{$account->id}/feed")
    ->withToken($account->token)
    ->execute('get');

// code 2
$account = auth()->user();
Http::prepare()
    ->withUrl("https://test.com/api/{$account->id}/profile")
    ->withToken($account->token)
    ->withBody('email', 'john@example.com')
    ->execute('patch');
```

It should be obvious that the two code samples share the same "baseUrl" (`https://test.com/api/{accountId}`) and attach an Authorization header on the request. You can create a separate class to eliminate this redundancy. A wrapper class only needs a `boot` method to work properly.

```php
use App\Clients;
use Plmrlnsnts\HttpExtended\PendingRequest;

class TestClient
{
    protected $account;

    public function __construct($account)
    {
        $this->account = $account;
    }

    public function boot(PendingRequest $request)
    {
        $request->baseUrl('https://test.com/api/' . $this->account->id);
        $request->withToken($this->account->token);
    }
}
```

We can shorten the previous code sample by passing the wrapper object that we just added to the `prepare` function of our requests.

```php
use App\Clients\TestClient;

// code 1
Http::prepare(new TestClient(auth()->user()))
    ->withUrl('/feed')
    ->execute('get');

// code 2
Http::prepare(new TestClient(auth()->user()))
    ->withUrl('/profile')
    ->withBody('email', 'john@example.com')
    ->execute('patch');
```

### Testing

``` bash
composer test
```

## Security

If you discover any security related issues, please email paulmarlonsantos@gmail.com instead of using the issue tracker.

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
