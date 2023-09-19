Cache
=====

.. image:: image.png
    :alt: Aplus Framework Cache Library

Aplus Framework Cache Library.

- `Installation`_
- `Getting Started`_
- `Set Values`_
- `Get Values`_
- `Increment and Decrement`_
- `Cache Handlers`_
- `Conclusion`_

Installation
------------

The installation of this library can be done with Composer:

.. code-block::

    composer require aplus/cache

Getting Started
---------------

The logic for caching values is similar to the example below:

.. code-block:: php

    use Framework\Cache\FilesCache;
    
    $cache = new FilesCache([
        'directory' => '/tmp/cache/'
    ]);
    
    $data = $cache->get('data'); // Data value or null
    
    if  ($data !== null) { // If data is cached, return now
        return $data;
    }
    
    $data = ['foo', 'bar']; // Else, set again
    $cache->set('data', $data, 15); // Cache for 15 seconds
    
    return $data;

If the value of "data" is cached it returns that value.

Otherwise, the value is added to the cache to be responded to in the next request,
within the 15 second TTL.

Set Values
----------

Items can be cached individually or several at a time.

.. code-block:: php

    // Set the value of "key" for 10 seconds
    $cache->set('key', 'value', 10); // bool

    // Set the values of "key" and "foo" for 10 seconds
    $cache->setMulti([
        'key'=> 'value',
        'foo'=> 'bar',
    ], 10); // array of booleans

The TTL can be set as the third argument of the ``set`` method or directly in
the ``defaultTtl`` property.

.. code-block:: php

    $cache->defaultTtl = 60;

Get Values
----------

Get values can also be individually or multiple at once:

.. code-block:: php

    // Data is the value of "key" or null
    $data = $cache->get('key'); 

    // Data is an array with the keys "key", "foo" and "baz"
    // Items not found have null value
    $data = $cache->getMulti(['key', 'foo', 'baz']);

Increment and Decrement
-----------------------

Some items may be simpler and only need to save increment or decrement values.
Example below:

.. code-block:: php

    $data = $cache->increment('foo'); // $data is 1
    $data = $cache->increment('foo'); // $data is 2
    $data = $cache->increment('foo', 3); // $data is 5

.. code-block:: php

    $data = $cache->decrement('foo'); // $data is -1
    $data = $cache->decrement('foo'); // $data is -2
    $data = $cache->decrement('foo', 3); // $data is -5

Cache Handlers
--------------

There are 3 cache handlers in the library and they are the following:

- `FilesCache`_
- `MemcachedCache`_
- `RedisCache`_

All handlers receive configs, prefix, serializer and logger through the constructor.

FilesCache
##########

The FilesCache config must have the value of ``directory``. The other configs
already have default values:

.. code-block:: php

    use Framework\Cache\FilesCache;

    $configs = [
        'directory' => '/patch/to/cache/directory',
        'files_permission' => 0644,
        'gc' => 1,
    ];
    $cache = new FilesCache($configs);

MemcachedCache
##############

The Memcached handler already comes with the configs set to connect to Memcache
on localhost.

If you want to set different configs, do as follows:

.. code-block:: php

    use Framework\Cache\MemcachedCache;

    $configs = [
         'servers' => [
            [
                'host' => '127.0.0.1',
                'port' => 11211,
                'weight' => 0,
            ],
            [
                'host' => '192.168.0.100',
                'port' => 11211,
                'weight' => 0,
            ],
        ],
        'options' => [
            Memcached::OPT_BINARY_PROTOCOL => true,
        ],
    ];
    $cache = new MemcachedCache($configs);

RedisCache
##########

The Redis handler is also already configured to work on localhost.

If it is necessary to define another address, do as in the example below:

.. code-block:: php

    use Framework\Cache\RedisCache;

    $configs = [
        'host' => '192.168.1.100',
        'port' => 6379,
        'timeout' => 0.0,
        'auth' => '',
    ];
    $cache = new RedisCache($configs);

Conclusion
----------

Aplus Cache Library is an easy-to-use tool for, beginners and experienced, PHP developers. 
With it you can optimize the performance of your applications. 
The more you use it, the more you will learn.

.. note::
    Did you find something wrong? 
    Be sure to let us know about it with an
    `issue <https://gitlab.com/aplus-framework/libraries/cache/issues>`_. 
    Thank you!
