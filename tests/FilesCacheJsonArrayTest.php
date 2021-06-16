<?php namespace Tests\Cache;

use Framework\Cache\Cache;

final class FilesCacheJsonArrayTest extends FilesCacheTest
{
	protected string $serializer = Cache::SERIALIZER_JSON_ARRAY;
}
