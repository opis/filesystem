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

namespace Opis\FileSystem\Directory;

use Opis\FileSystem\File\FileInfo;
use Opis\FileSystem\Handler\CachedHandler;
use Opis\FileSystem\ProtocolInfo;
use Opis\FileSystem\Traits\DirectoryFullPathTrait;

final class CachedDirectory implements Directory
{
    use DirectoryFullPathTrait;

    private ?Directory $directory;

    private ?CachedHandler $handler;

    private string $path;

    /**
     * CachedDirectory constructor.
     * @param Directory $directory
     * @param CachedHandler $handler
     */
    public function __construct(Directory $directory, CachedHandler $handler)
    {
        $this->directory = $directory;
        $this->handler = $handler;
        $this->path = $directory->path();

        if ($directory instanceof ProtocolInfo) {
            $this->protocol = $directory->protocol();
        }
    }

    /**
     * @inheritDoc
     */
    public function path(): string
    {
        return $this->path;
    }

    /**
     * @inheritDoc
     */
    public function doNext(): ?FileInfo
    {
        if ($this->directory === null) {
            return null;
        }

        if ($info = $this->directory->next()) {
            $this->handler->updateCache($info);
        }

        return $info;
    }

    /**
     * @inheritDoc
     */
    public function rewind(): bool
    {
        if ($this->directory === null) {
            return false;
        }

        return $this->directory->rewind();
    }

    /**
     * @inheritDoc
     */
    public function close(): void
    {
        if ($this->directory !== null) {
            $this->directory->close();
            $this->directory = null;
        }
    }

    /**
     * @inheritDoc
     */
    public function __destruct()
    {
        $this->close();
        $this->handler = null;
    }
}