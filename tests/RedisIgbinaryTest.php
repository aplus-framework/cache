<?php namespace Tests\Cache;

use Framework\Cache\Cache;

class RedisIgbinaryTest extends RedisTest
{
	protected $serializer = Cache::SERIALIZER_IGBINARY;
}
