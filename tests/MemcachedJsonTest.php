<?php namespace Tests\Cache;

use Framework\Cache\Cache;

final class MemcachedJsonTest extends MemcachedTest
{
	protected string $serializer = Cache::SERIALIZER_JSON;
}
