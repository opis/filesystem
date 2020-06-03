<?php
/* ============================================================================
 * Copyright 2019 Zindex Software
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *    http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 * ============================================================================ */

namespace Opis\FileSystem\Handler;

use RecursiveDirectoryIterator, RecursiveIteratorIterator, FilesystemIterator;
use Opis\Stream\{Stream, Printer\CopyPrinter};
use Opis\FileSystem\FileStream;
use Opis\FileSystem\Traits\SearchTrait;
use Opis\FileSystem\File\{FileInfo, Stat};
use Opis\FileSystem\Directory\{Directory, LocalDirectory};

class LocalFileHandler implements FileSystemHandler, AccessHandler, SearchHandler
{
    use SearchTrait;

    /** @var string */
    protected $root;
    /** @var string|null */
    protected $baseUrl;
    /** @var int */
    protected $defaultMode = 0777;

    /**
     * LocalFileHandler constructor.
     * @param string $root
     * @param null|string $base_url
     * @param int $default_mode
     */
    public function __construct(string $root, ?string $base_url = null, int $default_mode = 0777)
    {
        $this->root = realpath($root) . '/';
        if ($base_url !== null) {
            $this->baseUrl = rtrim($base_url, '/') . '/';
        }
        $this->defaultMode = $default_mode;
    }

    /**
     * @return string
     */
    public function root(): string
    {
        return $this->root;
    }

    /**
     * @param string $path
     * @return string
     */
    protected function fullPath(string $path): string
    {
        return $this->root . $path;
    }

    /**
     * @inheritDoc
     */
    public function mkdir(string $path, int $mode = 0777, bool $recursive = true): ?FileInfo
    {
        $fullPath = $this->fullPath($path);
        if (is_dir($fullPath)) {
            return null;
        }
        if (!@mkdir($fullPath, $mode, $recursive)) {
            return null;
        }
        return $this->info($path);
    }

    /**
     * @inheritDoc
     */
    public function rmdir(string $path, bool $recursive = true): bool
    {
        $path = $this->fullPath($path);
        if (!is_dir($path)) {
            return false;
        }

        if (!$recursive) {
            return @rmdir($path);
        }

        $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($path, FilesystemIterator::SKIP_DOTS),
            RecursiveIteratorIterator::CHILD_FIRST);

        foreach ($iterator as $filename => $fileInfo) {
            if ($fileInfo->isDir()) {
                if (!@rmdir($filename)) {
                    return false;
                }
            } elseif (!@unlink($filename)) {
                return false;
            }
        }

        return @rmdir($path);
    }

    /**
     * @inheritDoc
     */
    public function unlink(string $path): bool
    {
        return @unlink($this->fullPath($path));
    }

    /**
     * @inheritDoc
     */
    public function touch(string $path, int $time, ?int $atime = null): ?FileInfo
    {
        if (!$this->ensureDir($path, $this->defaultMode)) {
            return null;
        }

        if (!@touch($this->fullPath($path), $time, $atime ?? $time)) {
            return null;
        }

        return $this->info($path);
    }

    /**
     * @inheritDoc
     */
    public function chmod(string $path, int $mode): ?FileInfo
    {
        if (!@chmod($this->fullPath($path), $mode)) {
            return null;
        }
        return $this->info($path);
    }

    /**
     * @inheritDoc
     */
    public function chown(string $path, string $owner): ?FileInfo
    {
        if (!@chown($this->fullPath($path), $owner)) {
            return null;
        }
        return $this->info($path);
    }

    /**
     * @inheritDoc
     */
    public function chgrp(string $path, string $group): ?FileInfo
    {
        if (!@chgrp($this->fullPath($path), $group)) {
            return null;
        }
        return $this->info($path);
    }

    /**
     * @inheritDoc
     */
    public function rename(string $from, string $to): ?FileInfo
    {
        if ($from === $to) {
            return null;
        }
        if (!@rename($this->fullPath($from), $this->fullPath($to))) {
            return null;
        }
        return $this->info($to);
    }

    /**
     * @inheritDoc
     */
    public function copy(string $from, string $to, bool $overwrite = true): ?FileInfo
    {
        $from = trim($from, ' /');
        $to = trim($to, ' /');

        if ($from === '' || $from === $to) {
            return null;
        }

        $from_stat = $this->stat($from);
        if (!$from_stat) {
            return null;
        }

        $to_stat = $this->stat($to);

        if ($to_stat && !$overwrite) {
            return null;
        }

        if ($from_stat->isDir()) {
            $dir = $this->dir($from);
            if ($dir === null) {
                return null;
            }

            if ($to_stat) {
                if (!$to_stat->isDir()) {
                    if (!$this->unlink($to)) {
                        return null;
                    }
                    if (!$this->mkdir($to, $from_stat->mode(), true)) {
                        return null;
                    }
                }
            } elseif (!$this->mkdir($to, $from_stat->mode(), true)) {
                return null;
            }

            unset($from_stat, $to_stat);

            $ok = true;

            while ($ok && ($item = $dir->next())) {
                $name = $item->name();
                $ok = $this->copy($from . '/' . $name, $to . '/' . $name, $overwrite);
                unset($name, $item);
            }

            return $ok ? $this->info($to) : null;
        }

        if ($to_stat && $to_stat->isDir()) {
            if (!$this->rmdir($to)) {
                return null;
            }
        }

        unset($to_stat);

        return $this->write($to, $this->file($from, 'rb'), $from_stat->mode());
    }

    /**
     * @inheritDoc
     */
    public function stat(string $path, bool $resolve_links = true): ?Stat
    {
        $stat = $resolve_links ? @stat($this->fullPath($path)) : @lstat($this->fullPath($path));
        return $stat ? new Stat($stat) : null;
    }

    /**
     * @inheritDoc
     */
    public function write(string $path, Stream $stream, int $mode = 0777): ?FileInfo
    {
        if ($stream->size() === 0 && $stream->isEOF()) {
            $now = time();
            if ($this->touch($path, $now, $now)) {
                $this->chmod($path, $mode);
                return $this->info($path);
            }
            return null;
        }

        if (!$this->ensureDir($path, $this->defaultMode)) {
            return null;
        }

        if ($this->writeFile($path, $stream)) {
            $this->chmod($path, $mode);
            return $this->info($path);
        }

        return null;
    }

    /**
     * @inheritDoc
     */
    public function file(string $path, string $mode = 'rb'): ?Stream
    {
        if ($stat = $this->stat($path)) {
            if ($stat->isDir()) {
                return null;
            }
        }

        if (!$this->ensureDir($path, $this->defaultMode)) {
            return null;
        }

        try {
            return new FileStream($this->fullPath($path), $mode, $stat);
        } catch (\Throwable $e) {
            return null;
        }
    }

    /**
     * @inheritDoc
     */
    public function dir(string $path): ?Directory
    {
        $stat = $this->stat($path);
        if (!$stat || !$stat->isDir()) {
            return null;
        }
        return new LocalDirectory($this, $path, $this->root);
    }

    /**
     * @inheritDoc
     */
    public function info(string $path): ?FileInfo
    {
        $path = trim($path, ' /');
        if ($path === '') {
            return null;
        }

        $stat = $this->stat($path);
        if ($stat === null) {
            return null;
        }

        $url = null;
        if ($this->baseUrl !== null) {
            $url = $this->baseUrl . $path;
        }

        $type = null;

        if (!$stat->isDir()) {
            $type = mime_content_type($this->fullPath($path));
            if (!$type || $type === 'directory') {
                $type = null;
            }
        }

        return new FileInfo($path, $stat, $type, $url);
    }

    /**
     * @param string $path
     * @param int $mode
     * @return bool
     */
    protected function ensureDir(string $path, int $mode): bool
    {
        $path = trim($path, ' /');
        if ($path === '' || strpos($path, '/') === false) {
            return true;
        }

        $path = explode('/', $path);
        array_pop($path);
        $path = implode('/', $path);

        if ($stat = $this->stat($path, true)) {
            return $stat->isDir();
        }
        return $this->mkdir($path, $mode, true) !== null;
    }

    /**
     * @param string $path
     * @param Stream $stream
     * @return bool
     */
    protected function writeFile(string $path, Stream $stream): bool
    {
        $path = $this->fullPath($path);

        if ($resource = $stream->resource()) {
            if (get_resource_type($resource) === 'stream') {
                $to = @fopen($path, 'wb+');
                if (!$to) {
                    return false;
                }
                $res = stream_copy_to_stream($resource, $to);
                @fclose($to);
                return !($res === false);
            }
            unset($resource);
        }

        return (new CopyPrinter(new FileStream($path, 'wb+')))
                ->copy($stream) > 0;
    }
}