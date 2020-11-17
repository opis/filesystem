<?php
/* ============================================================================
 * Copyright 2019-2020 Zindex Software
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

use Opis\Stream\Stream;
use Opis\FileSystem\{FileInfo, Directory, Stat};

interface FileSystemHandler
{
    /**
     * @param string $path
     * @param int $mode
     * @param bool $recursive
     * @return null|\Opis\FileSystem\FileInfo
     */
    public function mkdir(string $path, int $mode = 0777, bool $recursive = true): ?FileInfo;

    /**
     * @param string $path
     * @param bool $recursive
     * @return bool
     */
    public function rmdir(string $path, bool $recursive = true): bool;

    /**
     * @param string $path
     * @return bool
     */
    public function unlink(string $path): bool;

    /**
     * @param string $from
     * @param string $to
     * @return null|\Opis\FileSystem\FileInfo
     */
    public function rename(string $from, string $to): ?FileInfo;

    /**
     * @param string $from
     * @param string $to
     * @param bool $overwrite
     * @return null|\Opis\FileSystem\FileInfo
     */
    public function copy(string $from, string $to, bool $overwrite = true): ?FileInfo;

    /**
     * @param string $path
     * @param bool $resolve_links
     * @return Stat|null
     */
    public function stat(string $path, bool $resolve_links = true): ?Stat;

    /**
     * @param string $path
     * @param Stream $stream
     * @param int $mode
     * @return null|FileInfo
     */
    public function write(string $path, Stream $stream, int $mode = 0777): ?FileInfo;

    /**
     * @param string $path
     * @param string $mode
     * @return Stream|null
     */
    public function file(string $path, string $mode = 'rb'): ?Stream;

    /**
     * @param string $path
     * @return Directory|null
     */
    public function dir(string $path): ?Directory;

    /**
     * @param string $path
     * @return FileInfo|null
     */
    public function info(string $path): ?FileInfo;
}