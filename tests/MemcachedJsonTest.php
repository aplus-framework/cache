<?php namespace Tests\Cache;

use Framework\Cache\Cache;

class MemcachedJsonTest extends MemcachedTest
{
	protected $serializer = Cache::SERIALIZER_JSON;
}
