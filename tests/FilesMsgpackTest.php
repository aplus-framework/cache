<?php namespace Tests\Cache;

use Framework\Cache\Cache;

final class FilesMsgpackTest extends FilesTest
{
	protected string $serializer = Cache::SERIALIZER_MSGPACK;
}
