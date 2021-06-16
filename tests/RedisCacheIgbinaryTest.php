<?php namespace Tests\Cache;

use Framework\Cache\Cache;

final class RedisCacheIgbinaryTest extends RedisCacheTest
{
	protected string $serializer = Cache::SERIALIZER_IGBINARY;
}
