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

namespace PhpProfiler\Lib\ByteStream;

use OutOfBoundsException;
use PhpProfiler\Lib\Process\MemoryMap\ProcessModuleMemoryMapInterface;
use PhpProfiler\Lib\Process\MemoryReader\MemoryReaderInterface;

final class ProcessMemoryByteReader implements ByteReaderInterface
{
    use ByteReaderDisableWriteAccessTrait;

    private const PAGE_SIZE = 8192;

    private MemoryReaderInterface $memory_reader;
    private int $pid;
    private ProcessModuleMemoryMapInterface $memory_map;
    /** @var CDataByteReader[] */
    private array $pages = [];

    /**
     * ProcessMemoryByteReader constructor.
     * @param MemoryReaderInterface $memory_reader
     * @param int $pid
     * @param ProcessModuleMemoryMapInterface $memory_map
     */
    public function __construct(
        MemoryReaderInterface $memory_reader,
        int $pid,
        ProcessModuleMemoryMapInterface $memory_map
    ) {
        $this->memory_reader = $memory_reader;
        $this->pid = $pid;
        $this->memory_map = $memory_map;
    }

    public function offsetExists($offset): bool
    {
        return $this->memory_map->isInRange($offset);
    }

    public function offsetGet($offset): int
    {
        if (!isset($this[$offset])) {
            throw new OutOfBoundsException();
        }

        $base_address = $this->memory_map->getBaseAddress();

        $page = (int)($offset / self::PAGE_SIZE);
        $page_block = $this->locatePage($page, $base_address);

        $diff = 0;
        if ($page * self::PAGE_SIZE < $base_address) {
            $diff = $base_address - $page * self::PAGE_SIZE;
        }

        return $page_block[($offset % self::PAGE_SIZE) - $diff];
    }

    private function locatePage(int $page, int $base_address): CDataByteReader
    {
        if (!isset($this->pages[$page])) {
            $this->pages[$page] = new CDataByteReader(
                $this->memory_reader->read(
                    $this->pid,
                    max($base_address, $page * self::PAGE_SIZE),
                    self::PAGE_SIZE
                )
            );
        }
        return $this->pages[$page];
    }

    public function createSliceAsString(int $offset, int $size): string
    {
        $result = '';
        for ($i = $offset; $i < ($offset + $size); $i++) {
            $result .= chr($this[$i]);
        }
        return $result;
    }
}
