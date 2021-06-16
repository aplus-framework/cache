<?php namespace Tests\Cache;

use Framework\Cache\Cache;

final class FilesCacheJsonTest extends FilesCacheTest
{
	protected string $serializer = Cache::SERIALIZER_JSON;
}
