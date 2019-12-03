# Cache Library *documentation*

The logic for caching values is similar to the example below:

```php
use Framework\Cache\Files;

$cache = new Files([
    'directory' => '/tmp/cache/'
]);

$data = $cache->get('data');

if  ($data !== null) { // If data is cached, return now
    return $data;
}

$data = ['foo', 'bar']; // Else, set again
$cache->set('data', $data, 15); // Cache for 15 seconds

return $data;
```

Since the required key has no values the cache is set again and will last the 
time of the configured Time To Live.

## Set values

Items can be cached individually or several at a time.

```php
// Set the value of "key" for 10 seconds
$cache->set('key', 'value', 10);
// Set the values of "key" and "foo" for 10 seconds
$cache->setMulti([
    'key'=> 'value',
    'foo'=> 'bar',
], 10);
```

## Get values

Get values can also be individually or multiple at once:

```php
$data = $cache->get('key'); // "value" or null
$data = $cache->getMulti(['key', 'foo', 'baz']);
```

## Incrementing and decrementing data

Some items may be simpler and only need to save increment or decrement values.
Example below:

```php
$data = $cache->increment('foo'); // $data = 1
$data = $cache->increment('foo'); // $data = 2
$data = $cache->increment('foo', 3); // $data = 5
```

```php
$data = $cache->decrement('foo'); // $data = -1
$data = $cache->decrement('foo'); // $data = -2
$data = $cache->decrement('foo', 3); // $data = -5
```
