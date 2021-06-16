<?php namespace Tests\Cache;

use Framework\Cache\Cache;

final class FilesCacheMsgpackTest extends FilesCacheTest
{
	protected string $serializer = Cache::SERIALIZER_MSGPACK;
}
