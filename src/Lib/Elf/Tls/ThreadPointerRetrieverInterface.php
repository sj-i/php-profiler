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

namespace PhpProfiler\Lib\Elf\Tls;

/**
 * Interface ThreadPointerRetrieverInterface
 * @package PhpProfiler\Lib\Elf\Tls
 */
interface ThreadPointerRetrieverInterface
{
    /**
     * @param int $pid
     * @return int
     * @throws TlsFinderException
     */
    public function getThreadPointer(int $pid): int;
}
