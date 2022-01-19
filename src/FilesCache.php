<?php declare(strict_types=1);
/*
 * This file is part of Aplus Framework Cache Library.
 *
 * (c) Natan Felles <natanfelles@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Framework\Cache;

use InvalidArgumentException;
use JetBrains\PhpStorm\Pure;
use RuntimeException;

/**
 * Class FilesCache.
 *
 * @package cache
 */
class FilesCache extends Cache
{
    /**
     * Files Cache handler configurations.
     *
     * @var array<string,mixed>
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

    public function __destruct()
    {
        if (\rand(1, 100) <= $this->configs['gc']) {
            $this->gc();
        }
    }

    protected function initialize() : void
    {
        $this->setBaseDirectory();
        $this->setGC($this->configs['gc']);
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
        if (isset($this->debugCollector)) {
            $start = \microtime(true);
            return $this->addDebugGet(
                $key,
                $start,
                $this->getContents($this->renderFilepath($key))
            );
        }
        return $this->getContents($this->renderFilepath($key));
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
        $value = @\file_get_contents($filepath);
        if ($value === false) {
            $this->log("Cache (files): File '{$filepath}' could not be read");
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

    public function set(string $key, mixed $value, int $ttl = null) : bool
    {
        if (isset($this->debugCollector)) {
            $start = \microtime(true);
            return $this->addDebugSet(
                $key,
                $ttl,
                $start,
                $value,
                $this->setValue($key, $value, $ttl)
            );
        }
        return $this->setValue($key, $value, $ttl);
    }

    public function setValue(string $key, mixed $value, int $ttl = null) : bool
    {
        $filepath = $this->renderFilepath($key);
        $this->createSubDirectory($filepath);
        $value = [
            'ttl' => \time() + $this->makeTtl($ttl),
            'data' => $value,
        ];
        $value = $this->serialize($value);
        $isFile = \is_file($filepath);
        $written = @\file_put_contents($filepath, $value, \LOCK_EX);
        if ($written !== false && $isFile === false) {
            \chmod($filepath, $this->configs['files_permission']);
        }
        if ($written === false) {
            $this->log("Cache (files): File '{$filepath}' could not be written");
            return false;
        }
        return true;
    }

    public function delete(string $key) : bool
    {
        if (isset($this->debugCollector)) {
            $start = \microtime(true);
            return $this->addDebugDelete(
                $key,
                $start,
                $this->deleteFile($this->renderFilepath($key))
            );
        }
        return $this->deleteFile($this->renderFilepath($key));
    }

    public function flush() : bool
    {
        if (isset($this->debugCollector)) {
            $start = \microtime(true);
            return $this->addDebugFlush(
                $start,
                $this->deleteAll($this->baseDirectory)
            );
        }
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

    protected function deleteExpired(string $baseDirectory) : bool
    {
        $handle = $this->openDir($baseDirectory);
        if ($handle === false) {
            return false;
        }
        $baseDirectory = \rtrim($baseDirectory, \DIRECTORY_SEPARATOR) . \DIRECTORY_SEPARATOR;
        $status = true;
        while (($path = \readdir($handle)) !== false) {
            if ($path[0] === '.') {
                continue;
            }
            $path = $baseDirectory . $path;
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

    protected function deleteAll(string $baseDirectory) : bool
    {
        $handle = $this->openDir($baseDirectory);
        if ($handle === false) {
            return false;
        }
        $baseDirectory = \rtrim($baseDirectory, \DIRECTORY_SEPARATOR) . \DIRECTORY_SEPARATOR;
        $status = true;
        while (($path = \readdir($handle)) !== false) {
            if ($path[0] === '.') {
                continue;
            }
            $path = $baseDirectory . $path;
            if (\is_file($path)) {
                if (\unlink($path)) {
                    continue;
                }
                $this->log("Cache (files): File '{$path}' could not be deleted");
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
            $deleted = \unlink($filepath);
            if ($deleted === false) {
                $this->log("Cache (files): File '{$filepath}' could not be deleted");
                return false;
            }
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

    #[Pure]
    protected function renderFilepath(string $key) : string
    {
        $key = \md5($key);
        return $this->baseDirectory .
            $key[0] . $key[1] . \DIRECTORY_SEPARATOR .
            $key;
    }
}
