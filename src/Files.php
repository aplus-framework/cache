<?php namespace Framework\Cache;

use InvalidArgumentException;
use RuntimeException;

/**
 * Class Files.
 */
class Files extends Cache
{
	/**
	 * Files Driver configurations.
	 *
	 * @var array|mixed[]
	 */
	protected array $configs = [
		'directory' => null,
		'files_permission' => 0644,
		'gc' => 1,
	];
	/**
	 * @var string|null
	 */
	protected ?string $baseDirectory;

	public function __construct(
		array $configs = [],
		string $prefix = null,
		string $serializer = 'php'
	) {
		parent::__construct($configs, $prefix, $serializer);
		$this->setBaseDirectory();
		$this->setGC($this->configs['gc']);
	}

	public function __destruct()
	{
		if (\rand(1, 100) <= $this->configs['gc']) {
			$this->gc();
		}
	}

	protected function setGC(int $gc) : void
	{
		if ($gc < 1 || $gc > 100) {
			throw new InvalidArgumentException(
				"Invalid cache GC: {$gc}"
			);
		}
	}

	protected function setBaseDirectory() : void
	{
		$path = $this->configs['directory'];
		if ($path === null) {
			$path = \sys_get_temp_dir();
		}
		$real = \realpath($path);
		if ($real === false) {
			throw new RuntimeException("Invalid cache directory: {$path}");
		}
		$real = \rtrim($path, \DIRECTORY_SEPARATOR) . \DIRECTORY_SEPARATOR;
		if (isset($this->prefix[0])) {
			$real .= $this->prefix;
		}
		if ( ! \is_dir($real)) {
			throw new RuntimeException(
				"Invalid cache directory path: {$real}"
			);
		}
		if ( ! \is_writable($real)) {
			throw new RuntimeException(
				"Cache directory is not writable: {$real}"
			);
		}
		$this->baseDirectory = $real . \DIRECTORY_SEPARATOR;
	}

	public function get(string $key) : mixed
	{
		$key = $this->renderFilepath($key);
		return $this->getContents($key);
	}

	/**
	 * @param string $filepath
	 *
	 * @return mixed
	 */
	protected function getContents(string $filepath) : mixed
	{
		if ( ! \is_file($filepath)) {
			return null;
		}
		$handle = @\fopen($filepath, 'rb');
		if ($handle === false) {
			return null;
		}
		\flock($handle, \LOCK_SH);
		$value = \fread($handle, \filesize($filepath));
		\flock($handle, \LOCK_UN);
		\fclose($handle);
		if ($value === false) {
			return null;
		}
		$value = (array) $this->unserialize($value);
		if ( ! isset($value['ttl'], $value['data']) || $value['ttl'] <= \time()) {
			$this->deleteFile($filepath);
			return null;
		}
		return $value['data'];
	}

	protected function createSubDirectory(string $filepath) : void
	{
		$dirname = \dirname($filepath);
		if (\is_dir($dirname)) {
			return;
		}
		if ( ! \mkdir($dirname, 0777, true) || ! \is_dir($dirname)) {
			throw new RuntimeException(
				"Directory key was not created: {$filepath}"
			);
		}
	}

	public function set(string $key, mixed $value, int $ttl = 60) : bool
	{
		$filepath = $this->renderFilepath($key);
		$this->createSubDirectory($filepath);
		$value = [
			'ttl' => \time() + $ttl,
			'data' => $value,
		];
		$value = $this->serialize($value);
		$is_file = \is_file($filepath);
		$handle = @\fopen($filepath, 'wb+');
		if ($handle === false) {
			return false;
		}
		\flock($handle, \LOCK_EX);
		$written = \fwrite($handle, $value);
		\flock($handle, \LOCK_UN);
		\fclose($handle);
		if ($is_file === false) {
			\chmod($filepath, $this->configs['files_permission']);
		}
		return $written !== false;
	}

	public function delete(string $key) : bool
	{
		$key = $this->renderFilepath($key);
		return $this->deleteFile($key);
	}

	public function flush() : bool
	{
		return $this->deleteAll($this->baseDirectory);
	}

	/**
	 * Garbage collector.
	 *
	 * Deletes all expired items.
	 *
	 * @return bool TRUE if all expired items was deleted, FALSE if a fail occurs
	 */
	public function gc() : bool
	{
		return $this->deleteExpired($this->baseDirectory);
	}

	protected function deleteExpired(string $base_directory) : bool
	{
		$handle = $this->openDir($base_directory);
		if ($handle === false) {
			return false;
		}
		$base_directory = \rtrim($base_directory, \DIRECTORY_SEPARATOR) . \DIRECTORY_SEPARATOR;
		$status = true;
		while (($path = \readdir($handle)) !== false) {
			if ($path[0] === '.') {
				continue;
			}
			$path = $base_directory . $path;
			if (\is_file($path)) {
				$this->getContents($path);
				continue;
			}
			if ( ! $this->deleteExpired($path)) {
				$status = false;
				break;
			}
			if (\scandir($path, \SCANDIR_SORT_ASCENDING) === ['.', '..'] && ! \rmdir($path)) {
				$status = false;
				break;
			}
		}
		$this->closeDir($handle);
		return $status;
	}

	protected function deleteAll(string $base_directory) : bool
	{
		$handle = $this->openDir($base_directory);
		if ($handle === false) {
			return false;
		}
		$base_directory = \rtrim($base_directory, \DIRECTORY_SEPARATOR) . \DIRECTORY_SEPARATOR;
		$status = true;
		while (($path = \readdir($handle)) !== false) {
			if ($path[0] === '.') {
				continue;
			}
			$path = $base_directory . $path;
			if (\is_file($path)) {
				if (\unlink($path)) {
					continue;
				}
				$status = false;
				break;
			}
			if ( ! $this->deleteAll($path)) {
				$status = false;
				break;
			}
			if (\scandir($path, \SCANDIR_SORT_ASCENDING) === ['.', '..'] && ! \rmdir($path)) {
				$status = false;
				break;
			}
		}
		$this->closeDir($handle);
		return $status;
	}

	protected function deleteFile(string $filepath) : bool
	{
		if (\is_file($filepath)) {
			return \unlink($filepath);
		}
		return true;
	}

	/**
	 * @param string $dirpath
	 *
	 * @return false|resource
	 */
	protected function openDir(string $dirpath)
	{
		$real = \realpath($dirpath);
		if ($real === false) {
			return false;
		}
		if ( ! \is_dir($real)) {
			return false;
		}
		$real = \rtrim($real, \DIRECTORY_SEPARATOR) . \DIRECTORY_SEPARATOR;
		if ( ! \str_starts_with($real, $this->configs['directory'])) {
			return false;
		}
		return \opendir($real);
	}

	/**
	 * @param resource $resource
	 */
	protected function closeDir($resource) : void
	{
		if (\is_resource($resource)) {
			\closedir($resource);
		}
	}

	protected function renderFilepath(string $key) : string
	{
		$key = \md5($key);
		return $this->baseDirectory .
			$key[0] . $key[1] . \DIRECTORY_SEPARATOR .
			$key;
	}
}
