<?php

/**
 * This file is part of the sj-i/php-profiler package.
 *
 * (c) sji <sji@sj-i.dev>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace PhpProfiler\Lib\PhpInternals;

final class ZendTypeReaderCreator
{
    /**
     * @param string $php_version
     * @psalm-param value-of<ZendTypeReader::ALL_SUPPORTED_VERSIONS> $php_version
     * @return ZendTypeReader
     */
    public function create(string $php_version): ZendTypeReader
    {
        return new ZendTypeReader($php_version);
    }
}
