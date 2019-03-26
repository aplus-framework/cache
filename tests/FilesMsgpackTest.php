<?php namespace Tests\Cache;

use Framework\Cache\Cache;

class FilesMsgpackTest extends FilesTest
{
	protected $serializer = Cache::SERIALIZER_MSGPACK;
}
