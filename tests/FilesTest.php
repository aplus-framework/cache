<?php namespace Tests\Cache;

use Framework\Cache\Files;

class FilesTest extends TestCase
{
	/**
	 * @var array
	 */
	protected $configs = [
		'directory' => '/tmp/cache/',
	];

	public function setUp()
	{
		\exec('rm -rf ' . $this->configs['directory']);
		\exec('mkdir -p ' . $this->configs['directory'] . $this->prefix);
		$this->cache = new Files($this->configs, $this->prefix, $this->serializer);
	}

	public function tearDown()
	{
		if (\is_dir($this->configs['directory'])) {
			\chmod($this->configs['directory'], 0777);
		}
		parent::tearDown();
	}

	public function testInvalidGCValue()
	{
		$this->configs['gc'] = 0;
		$this->expectException(\InvalidArgumentException::class);
		$this->expectExceptionMessage('Invalid cache GC: 0');
		new Files($this->configs, $this->prefix, $this->serializer);
	}

	public function testInvalidCacheDirectory()
	{
		$this->configs['directory'] = '/foo';
		$this->expectException(\RuntimeException::class);
		$this->expectExceptionMessage('Invalid cache directory: /foo');
		new Files($this->configs, $this->prefix, $this->serializer);
	}

	public function testInvalidCacheDirectoryPath()
	{
		$this->prefix = 'foo';
		$this->expectException(\RuntimeException::class);
		$this->expectExceptionMessage(
			"Invalid cache directory path: {$this->configs['directory']}{$this->prefix}"
		);
		new Files($this->configs, $this->prefix, $this->serializer);
	}

	public function testCacheDirectoryIsNotWritable()
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
}
