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

use RuntimeException;

/**
 * Class ApcuCache.
 *
 * @package cache
 *
 * @since 4.3
 */
class ApcuCache extends Cache
{
    protected function initialize() : void
    {
        if (!\apcu_enabled()) {
            throw new RuntimeException('APCu extension is not enabled');
        }
    }

    public function get(string $key) : mixed
    {
        if (isset($this->debugCollector)) {
            $start = \microtime(true);
            return $this->addDebugGet(
                $key,
                $start,
                $this->getValue($key)
            );
        }
        return $this->getValue($key);
    }

    protected function getValue(string $key) : mixed
    {
        $key = \apcu_fetch($this->renderKey($key), $success);
        return $success
            ? $this->unserialize($key)
            : null;
    }

    public function set(string $key, mixed $value, ?int $ttl = null) : bool
    {
        if (isset($this->debugCollector)) {
            $start = \microtime(true);
            return $this->addDebugSet(
                $key,
                $ttl,
                $start,
                $value,
                \apcu_store(
                    $this->renderKey($key),
                    $this->serialize($value),
                    $this->makeTtl($ttl)
                )
            );
        }
        return \apcu_store(
            $this->renderKey($key),
            $this->serialize($value),
            $this->makeTtl($ttl)
        );
    }

    public function delete(string $key) : bool
    {
        if (isset($this->debugCollector)) {
            $start = \microtime(true);
            return $this->addDebugDelete(
                $key,
                $start,
                \apcu_delete($this->renderKey($key))
            );
        }
        return \apcu_delete($this->renderKey($key));
    }

    public function flush() : bool
    {
        if (isset($this->debugCollector)) {
            $start = \microtime(true);
            return $this->addDebugFlush(
                $start,
                \apcu_clear_cache()
            );
        }
        return \apcu_clear_cache();
    }
}
