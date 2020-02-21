<?php namespace Tests\Cache;

use Framework\Cache\Cache;

class RedisIgbinaryTest extends RedisTest
{
	protected string $serializer = Cache::SERIALIZER_IGBINARY;
}
