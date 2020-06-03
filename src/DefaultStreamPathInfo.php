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

use Opis\FileSystem\Handler\FileSystemHandler;

final class DefaultStreamPathInfo implements FileSystemStreamPathInfo
{

    private FileSystemHandler $handler;

    private string $path;

    /**
     * @param FileSystemHandler $handler
     * @param string $path
     */
    public function __construct(FileSystemHandler $handler, string $path)
    {
        $this->handler = $handler;
        $this->path = $path;
    }

    /**
     * @inheritDoc
     */
    public function handler(): FileSystemHandler
    {
        return $this->handler;
    }

    /**
     * @inheritDoc
     */
    public function path(): string
    {
        return $this->path;
    }
}