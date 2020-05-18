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

namespace PhpProfiler\ProcessReader;

use PhpProfiler\Lib\Elf\Parser\ElfParserException;
use PhpProfiler\Lib\Elf\Process\ProcessModuleSymbolReader;
use PhpProfiler\Lib\Elf\Process\ProcessModuleSymbolReaderCreator;
use PhpProfiler\Lib\Elf\Process\ProcessSymbolReaderException;
use PhpProfiler\Lib\Elf\SymbolResolver\Elf64SymbolResolverCreator;
use PhpProfiler\Lib\Elf\Tls\LibThreadDbTlsFinder;
use PhpProfiler\Lib\Elf\Tls\TlsFinderException;
use PhpProfiler\Lib\Elf\Tls\X64LinuxThreadPointerRetriever;
use PhpProfiler\Lib\File\CatFileReader;
use PhpProfiler\Lib\Process\MemoryMap\ProcessMemoryMapCreator;
use PhpProfiler\Lib\Process\MemoryReader\MemoryReaderException;
use PhpProfiler\Lib\Process\MemoryReader\MemoryReaderInterface;

/**
 * Class PhpSymbolReaderCreator
 * @package PhpProfiler\ProcessReader
 */
final class PhpSymbolReaderCreator
{
    private MemoryReaderInterface $memory_reader;

    /**
     * PhpSymbolReaderCreator constructor.
     * @param MemoryReaderInterface $memory_reader
     */
    public function __construct(MemoryReaderInterface $memory_reader)
    {
        $this->memory_reader = $memory_reader;
    }

    /**
     * @param int $pid
     * @param string $libpthread_finder_regex
     * @param string $php_finder_regex
     * @return ProcessModuleSymbolReader
     * @throws ElfParserException
     * @throws MemoryReaderException
     * @throws ProcessSymbolReaderException
     * @throws TlsFinderException
     */
    public function create(
        int $pid,
        string $libpthread_finder_regex = '/.*\/libpthread.*\.so$/',
        string $php_finder_regex = '/.*\/(php(74|7.4|80|8.0)?|php-fpm|libphp[78].*\.so)$/'
    ): ProcessModuleSymbolReader {
        $memory_reader = $this->memory_reader;

        $symbol_reader_creator = new ProcessModuleSymbolReaderCreator(
            new Elf64SymbolResolverCreator(new CatFileReader()),
            $memory_reader
        );
        $process_memory_map = ProcessMemoryMapCreator::create()->getProcessMemoryMap($pid);

        $tls_block_address = null;
        $libpthread_symbol_reader = $symbol_reader_creator->createModuleReaderByNameRegex(
            $pid,
            $process_memory_map,
            $libpthread_finder_regex
        );
        if (!is_null($libpthread_symbol_reader) and $libpthread_symbol_reader->isAllSymbolResolvable()) {
            $tls_finder = new LibThreadDbTlsFinder(
                $libpthread_symbol_reader,
                X64LinuxThreadPointerRetriever::createDefault(),
                $memory_reader
            );
            $tls_block_address = $tls_finder->findTlsBlock($pid, 1);
        }

        $php_symbol_reader = $symbol_reader_creator->createModuleReaderByNameRegex(
            $pid,
            $process_memory_map,
            $php_finder_regex,
            $tls_block_address
        );
        if (is_null($php_symbol_reader)) {
            throw new \RuntimeException('php module not found');
        }
        return $php_symbol_reader;
    }
}
