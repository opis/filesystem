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

namespace Opis\FileSystem;

use Opis\FileSystem\Handler\{
    AccessHandler,
    FileSystemHandler,
    SearchHandler
};

interface MountManager extends FileSystemHandlerManager, FileSystemHandler, AccessHandler, SearchHandler
{
    /**
     * @param string $name
     * @param FileSystemHandler $handler
     * @return bool
     */
    public function mount(string $name, FileSystemHandler $handler): bool;

    /**
     * @param string $name
     * @return bool
     */
    public function umount(string $name): bool;

    /**
     * @param string $name
     * @return null|FileSystemHandler
     */
    public function handler(string $name): ?FileSystemHandler;

    /**
     * @return iterable|FileSystemHandler[]
     */
    public function handlers(): iterable;

    /**
     * @param string $from
     * @param string $to
     * @param bool $recursive
     * @param bool $overwrite
     * @param callable|null $filter
     * @return int|null
     */
    public function copyFiltered(
        string $from,
        string $to,
        bool $recursive = true,
        bool $overwrite = true,
        ?callable $filter = null
    ): ?int;

    /**
     * @param string $source
     * @param string $replica
     * @param callable|null $filter
     * @return int|null
     */
    public function sync(string $source, string $replica, ?callable $filter = null): ?int;

    /**
     * @param string $path
     * @return bool
     */
    public function exists(string $path): bool;

    /**
     * @param string $path
     * @return bool
     */
    public function isFile(string $path): bool;

    /**
     * @param string $path
     * @return bool
     */
    public function isDir(string $path): bool;

    /**
     * @param string $path
     * @return bool
     */
    public function isLink(string $path): bool;
}