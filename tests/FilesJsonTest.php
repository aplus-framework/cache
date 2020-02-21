<?php namespace Tests\Cache;

use Framework\Cache\Cache;

class FilesJsonTest extends FilesTest
{
	protected string $serializer = Cache::SERIALIZER_JSON;
}
