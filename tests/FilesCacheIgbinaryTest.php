<?php namespace Tests\Cache;

use Framework\Cache\Cache;

final class FilesCacheIgbinaryTest extends FilesCacheTest
{
	protected string $serializer = Cache::SERIALIZER_IGBINARY;
}
