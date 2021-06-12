<?php namespace Tests\Cache;

use Framework\Cache\Cache;

final class FilesJsonArrayTest extends FilesTest
{
	protected string $serializer = Cache::SERIALIZER_JSON_ARRAY;
}
