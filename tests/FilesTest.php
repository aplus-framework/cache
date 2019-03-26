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
}
