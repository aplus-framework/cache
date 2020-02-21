<?php namespace Tests\Cache;

use Framework\Cache\Cache;

class FilesMsgpackTest extends FilesTest
{
	protected string $serializer = Cache::SERIALIZER_MSGPACK;
}
