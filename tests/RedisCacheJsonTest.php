<?php namespace Tests\Cache;

use Framework\Cache\Cache;

final class RedisCacheJsonTest extends RedisCacheTest
{
	protected string $serializer = Cache::SERIALIZER_JSON;
}
