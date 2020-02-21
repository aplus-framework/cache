<?php namespace Tests\Cache;

use Framework\Cache\Cache;

class FilesJsonArrayTest extends FilesTest
{
	protected string $serializer = Cache::SERIALIZER_JSON_ARRAY;
}
