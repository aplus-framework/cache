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

use Framework\Cache\Cache;

final class FilesCacheJsonTest extends FilesCacheTest
{
	protected string $serializer = Cache::SERIALIZER_JSON;
}
