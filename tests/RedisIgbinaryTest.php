<?php namespace Tests\Cache;

use Framework\Cache\Cache;

final class RedisIgbinaryTest extends RedisTest
{
	protected string $serializer = Cache::SERIALIZER_IGBINARY;
}
