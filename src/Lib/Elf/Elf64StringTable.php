<?php

/**
 * This file is part of the sj-i/php-profiler package.
 *
 * (c) sji <sji@sj-i.dev>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


namespace PhpProfiler\Lib\Elf;

/**
 * Class Elf64StringTable
 * @package PhpProfiler\Lib\Elf
 */
class Elf64StringTable
{
    /** @var string[] */
    public $strings = [];

    /**
     * Elf64StringTable constructor.
     * @param string ...$strings
     */
    public function __construct(string ...$strings)
    {
        $this->strings = $strings;
    }
}