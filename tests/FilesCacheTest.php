<?php
/*
 * This file is part of The Framework Cache Library.
 *
 * (c) Natan Felles <natanfelles@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Tests\Cache;

use Framework\Cache\FilesCache;

class FilesCacheTest extends TestCase
{
	protected array $configs = [
		'directory' => '/tmp/cache/',
		'gc' => 100,
	];

	public function setUp() : void
	{
		\exec('rm -rf ' . $this->configs['directory']);
		\exec('mkdir -p ' . $this->configs['directory'] . $this->prefix);
		$this->cache = new FilesCache($this->configs, $this->prefix, $this->serializer);
	}

	public function tearDown() : void
	{
		if (\is_dir($this->configs['directory'])) {
			\chmod($this->configs['directory'], 0777);
		}
		parent::tearDown();
	}

	public function testGC() : void
	{
		$this->cache->set('foo', 'bar', 1);
		$this->cache->set('bar', 'baz', 2);
		\sleep(1);
		self::assertTrue($this->cache->gc()); // @phpstan-ignore-line
		self::assertNull($this->cache->get('foo'));
		self::assertSame('baz', $this->cache->get('bar'));
	}

	public function testInvalidGCValue() : void
	{
		$this->configs['gc'] = 0;
		$this->expectException(\InvalidArgumentException::class);
		$this->expectExceptionMessage('Invalid cache GC: 0');
		new FilesCache($this->configs, $this->prefix, $this->serializer);
	}

	public function testInvalidCacheDirectory() : void
	{
		$this->configs['directory'] = '/foo';
		$this->expectException(\RuntimeException::class);
		$this->expectExceptionMessage('Invalid cache directory: /foo');
		new FilesCache($this->configs, $this->prefix, $this->serializer);
	}

	public function testInvalidCacheDirectoryPath() : void
	{
		$this->prefix = 'foo';
		$this->expectException(\RuntimeException::class);
		$this->expectExceptionMessage(
			"Invalid cache directory path: {$this->configs['directory']}{$this->prefix}"
		);
		new FilesCache($this->configs, $this->prefix, $this->serializer);
	}

	public function testDefaultConfigs() : void
	{
		self::assertInstanceOf(FilesCache::class, new FilesCache());
	}

	public function testInvalidSerializer() : void
	{
		$this->expectException(\InvalidArgumentException::class);
		$this->expectExceptionMessage(
			'Invalid serializer: foo'
		);
		new FilesCache($this->configs, $this->prefix, 'foo');
	}

	public function testCacheDirectoryIsNotWritable() : void
	{
		if (\getenv('GITLAB_CI')) {
			$this->markTestIncomplete();
		}
		\chmod($this->configs['directory'], 0400);
		$this->expectException(\RuntimeException::class);
		$this->expectExceptionMessage(
			"Cache directory is not writable: {$this->configs['directory']}{$this->prefix}"
		);
		new FilesCache($this->configs, $this->prefix, $this->serializer);
	}

	public function testSetFailure() : void
	{
		if (\getenv('GITLAB_CI')) {
			$this->markTestIncomplete();
		}
		self::assertTrue($this->cache->set('key', 'value'));
		\exec('chmod 444 ' . $this->configs['directory'] . '*');
		self::assertFalse($this->cache->set('key', 'value'));
		\exec('chmod 777 ' . $this->configs['directory'] . '*');
	}

	public function testGetFailure() : void
	{
		if (\getenv('GITLAB_CI')) {
			$this->markTestIncomplete();
		}
		self::assertTrue($this->cache->set('key', 'value'));
		self::assertSame('value', $this->cache->get('key'));
		\exec('chmod 444 ' . $this->configs['directory'] . '*');
		self::assertNull($this->cache->get('key'));
		\exec('chmod 777 ' . $this->configs['directory'] . '*');
	}

	public function testGetInvalidContents() : void
	{
		self::assertTrue($this->cache->set('key', 'value'));
		foreach ((array) \glob($this->configs['directory'] . '*/*/*') as $file) {
			\file_put_contents((string) $file, '');
		}
		self::assertNull($this->cache->get('key'));
	}
}
