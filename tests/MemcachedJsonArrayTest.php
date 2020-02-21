<?php namespace Tests\Cache;

use Framework\Cache\Cache;

class MemcachedJsonArrayTest extends MemcachedTest
{
	protected string $serializer = Cache::SERIALIZER_JSON_ARRAY;
}
