<?php

/**
 * This file is part of the sj-i/php-profiler package.
 *
 * (c) sji <sji@sj-i.dev>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


namespace PhpProfiler\ProcessReader;

/**
 * Class ProcessMemoryMap
 * @package PhpProfiler\ProcessReader
 */
class ProcessMemoryMap
{
    /**
     * @var ProcessMemoryArea[]
     */
    private array $memory_areas;

    /**
     * ProcessMemoryMap constructor.
     * @param ProcessMemoryArea[] $memory_areas
     */
    public function __construct(array $memory_areas)
    {
        $this->memory_areas = $memory_areas;
    }
}