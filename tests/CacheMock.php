<?php declare(strict_types=1);
/*
 * This file is part of Aplus Framework Cache Library.
 *
 * (c) Natan Felles <natanfelles@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Tests\Cache;

use Framework\Cache\Cache;

/**
 * Class CacheMock.
 *
 * @package cache
 */
class CacheMock extends Cache
{
    public function get(string $key) : mixed
    {
        return 1;
    }

    public function set(string $key, mixed $value, int $ttl = null) : bool
    {
        return true;
    }

    public function delete(string $key) : bool
    {
        return true;
    }

    public function flush() : bool
    {
        return true;
    }
}
