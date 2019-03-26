<?php namespace Tests\Cache;

use Framework\Cache\Cache;

class FilesIgbinaryTest extends FilesTest
{
	protected $serializer = Cache::SERIALIZER_IGBINARY;
}
