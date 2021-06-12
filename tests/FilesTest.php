<?php namespace Tests\Cache;

use Framework\Cache\Files;

class FilesTest extends TestCase
{
	protected array $configs = [
		'directory' => '/tmp/cache/',
		'gc' => 100,
	];

	public function setUp() : void
	{
		\exec('rm -rf ' . $this->configs['directory']);
		\exec('mkdir -p ' . $this->configs['directory'] . $this->prefix);
		$this->cache = new Files($this->configs, $this->prefix, $this->serializer);
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
		$this->assertTrue($this->cache->gc());
		$this->assertNull($this->cache->get('foo'));
		$this->assertEquals('baz', $this->cache->get('bar'));
	}

	public function testInvalidGCValue() : void
	{
		$this->configs['gc'] = 0;
		$this->expectException(\InvalidArgumentException::class);
		$this->expectExceptionMessage('Invalid cache GC: 0');
		new Files($this->configs, $this->prefix, $this->serializer);
	}

	public function testInvalidCacheDirectory() : void
	{
		$this->configs['directory'] = '/foo';
		$this->expectException(\RuntimeException::class);
		$this->expectExceptionMessage('Invalid cache directory: /foo');
		new Files($this->configs, $this->prefix, $this->serializer);
	}

	public function testInvalidCacheDirectoryPath() : void
	{
		$this->prefix = 'foo';
		$this->expectException(\RuntimeException::class);
		$this->expectExceptionMessage(
			"Invalid cache directory path: {$this->configs['directory']}{$this->prefix}"
		);
		new Files($this->configs, $this->prefix, $this->serializer);
	}

	public function testDefaultConfigs() : void
	{
		$this->assertInstanceOf(Files::class, new Files());
	}

	public function testInvalidSerializer() : void
	{
		$this->expectException(\InvalidArgumentException::class);
		$this->expectExceptionMessage(
			'Invalid serializer: foo'
		);
		new Files($this->configs, $this->prefix, 'foo');
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
		new Files($this->configs, $this->prefix, $this->serializer);
	}

	public function testSetFailure() : void
	{
		if (\getenv('GITLAB_CI')) {
			$this->markTestIncomplete();
		}
		$this->assertTrue($this->cache->set('key', 'value'));
		\exec('chmod 444 ' . $this->configs['directory'] . '*');
		$this->assertFalse($this->cache->set('key', 'value'));
		\exec('chmod 777 ' . $this->configs['directory'] . '*');
	}

	public function testGetFailure() : void
	{
		if (\getenv('GITLAB_CI')) {
			$this->markTestIncomplete();
		}
		$this->assertTrue($this->cache->set('key', 'value'));
		$this->assertEquals('value', $this->cache->get('key'));
		\exec('chmod 444 ' . $this->configs['directory'] . '*');
		$this->assertNull($this->cache->get('key'));
		\exec('chmod 777 ' . $this->configs['directory'] . '*');
	}

	public function testGetInvalidContents() : void
	{
		$this->assertTrue($this->cache->set('key', 'value'));
		foreach (\glob($this->configs['directory'] . '*/*/*') as $file) {
			\file_put_contents($file, '');
		}
		$this->assertNull($this->cache->get('key'));
	}
}
