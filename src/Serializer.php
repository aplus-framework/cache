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

/**
 * Enum Serializer.
 *
 * @package cache
 */
enum Serializer : string
{
    /**
     * The Igbinary serializer.
     */
    case IGBINARY = 'igbinary';
    /**
     * The JSON serializer.
     */
    case JSON = 'json';
    /**
     * The JSON Array serializer.
     */
    case JSON_ARRAY = 'json-array';
    /**
     * The MessagePack serializer.
     */
    case MSGPACK = 'msgpack';
    /**
     * The PHP serializer.
     */
    case PHP = 'php';
}
