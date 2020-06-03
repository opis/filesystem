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

use Iterator;
use Opis\FileSystem\File\FileInfo;
use Opis\FileSystem\ProtocolInfo;
use Opis\FileSystem\Traits\DirectoryFullPathTrait;

class IteratorDirectory implements Directory, ProtocolInfo
{
    use DirectoryFullPathTrait;

    protected string $path;

    /** @var Iterator|FileInfo[] */
    protected ?Iterator $iterator;

    /**
     * @param string $path
     * @param Iterator|FileInfo[] $iterator
     */
    public function __construct(string $path, Iterator $iterator)
    {
        $this->path = trim($path, ' /');
        $this->iterator = $iterator;
        $this->iterator->rewind();
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
        if ($this->iterator === null || !$this->iterator->valid()) {
            return null;
        }

        $next = $this->iterator->current();

        $this->iterator->next();

        return $next instanceof FileInfo ? $next : null;
    }

    /**
     * @inheritDoc
     */
    public function rewind(): bool
    {
        if ($this->iterator === null) {
            return false;
        }

        $this->iterator->rewind();

        return (bool)$this->iterator->valid();
    }

    /**
     * @inheritDoc
     */
    public function close(): void
    {
        $this->iterator = null;
    }
}