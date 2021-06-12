<?php namespace Tests\Cache;

use Framework\Cache\Cache;

final class FilesIgbinaryTest extends FilesTest
{
	protected string $serializer = Cache::SERIALIZER_IGBINARY;
}
