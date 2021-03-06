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

namespace PhpProfiler\Lib\PhpProcessReader;

use PHPUnit\Framework\TestCase;

/**
 * Class PhpBinaryFinderTest
 * @package PhpProfiler\ProcessReader
 */
class PhpBinaryFinderTest extends TestCase
{
    public function testFindByProcessId()
    {
        $finder = new PhpBinaryFinder();
        $path = $finder->findByProcessId(getmypid());
        $this->assertStringContainsString('php', $path);
    }
}
