<?php namespace Tests\Cache;

use Framework\Cache\Cache;

class FilesJsonArrayTest extends FilesTest
{
	protected $serializer = Cache::SERIALIZER_JSON_ARRAY;
}
