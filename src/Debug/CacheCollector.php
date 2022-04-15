<?php declare(strict_types=1);
/*
 * This file is part of Aplus Framework Cache Library.
 *
 * (c) Natan Felles <natanfelles@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Framework\Cache\Debug;

use Framework\Cache\FilesCache;
use Framework\Cache\MemcachedCache;
use Framework\Cache\RedisCache;
use Framework\Debug\Collector;

/**
 * Class CacheCollector.
 *
 * @package cache
 */
class CacheCollector extends Collector
{
    /**
     * @var array<string,mixed>
     */
    protected array $info;

    /**
     * @param array<string,mixed> $info
     *
     * @return static
     */
    public function setInfo(array $info) : static
    {
        $this->info = $info;
        return $this;
    }

    public function getActivities() : array
    {
        $activities = [];
        foreach ($this->getData() as $index => $data) {
            $activities[] = [
                'collector' => $this->getName(),
                'class' => static::class,
                'description' => 'Run command ' . ($index + 1),
                'start' => $data['start'],
                'end' => $data['end'],
            ];
        }
        return $activities;
    }

    public function getContents() : string
    {
        if (empty($this->info)) {
            return '<p>This collector has not been added to a Cache instance.</p>';
        }
        \ob_start(); ?>
        <p><strong>Handler:</strong> <?= $this->getHandler() ?></p>
        <p><strong>Serializer:</strong> <?= $this->info['serializer'] ?></p>
        <h1>Commands</h1>
        <?php
        echo $this->renderCommands();
        return \ob_get_clean(); // @phpstan-ignore-line
    }

    protected function renderCommands() : string
    {
        if ( ! $this->hasData()) {
            return '<p>No command was run.</p>';
        }
        $count = \count($this->getData());
        \ob_start(); ?>
        <p>Ran <?= $count ?> command<?= $count === 1 ? '' : 's' ?>:</p>
        <table>
            <thead>
            <tr>
                <th>#</th>
                <th>Command</th>
                <th>Status</th>
                <th>Key</th>
                <th>Value</th>
                <th title="Time To Live in seconds">TTL</th>
                <th>Expires At</th>
                <th title="Seconds">Time</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($this->getData() as $index => $data): ?>
                <tr>
                    <td><?= $index + 1 ?></td>
                    <td><?= \htmlentities($data['command']) ?></td>
                    <td><?= \htmlentities($data['status']) ?></td>
                    <td><?= \htmlentities($data['key'] ?? '') ?></td>
                    <td>
                        <?php if (isset($data['value'])): ?>
                            <pre><code class="language-php"><?= \htmlentities($data['value']) ?></code></pre>
                        <?php endif ?>
                    </td>
                    <td><?= \htmlentities((string) ($data['ttl'] ?? '')) ?></td>
                    <td><?php
                        if (isset($data['ttl'])) {
                            $ttl = $data['start'] + $data['ttl'];
                            echo \date('Y-m-d H:i:s', (int) $ttl);
                        } ?></td>
                    <td><?= \round($data['end'] - $data['start'], 6) ?></td>
                </tr>
            <?php endforeach ?>
            </tbody>
        </table>
        <?php
        return \ob_get_clean(); // @phpstan-ignore-line
    }

    protected function getHandler() : string
    {
        foreach ([
            'files' => FilesCache::class,
            'memcached' => MemcachedCache::class,
            'redis' => RedisCache::class,
        ] as $name => $class) {
            if ($this->info['class'] === $class) {
                return $name;
            }
        }
        return $this->info['class'];
    }
}
