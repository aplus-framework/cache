<?php namespace Tests\Cache;

use Framework\Cache\Cache;

class FilesJsonTest extends FilesTest
{
	protected $serializer = Cache::SERIALIZER_JSON;
}
