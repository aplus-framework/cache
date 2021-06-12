<?php namespace Tests\Cache;

use Framework\Cache\Cache;

final class FilesJsonTest extends FilesTest
{
	protected string $serializer = Cache::SERIALIZER_JSON;
}
