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

namespace PhpProfiler\Inspector\Daemon\Reader\Worker;

use Generator;
use PhpProfiler\Inspector\Daemon\Reader\Protocol\Message\TraceMessage;
use PhpProfiler\Inspector\Settings\GetTraceSettings\GetTraceSettings;
use PhpProfiler\Inspector\Settings\TargetPhpSettings\TargetPhpSettings;
use PhpProfiler\Inspector\Settings\TargetProcessSettings\TargetProcessSettings;
use PhpProfiler\Inspector\Settings\TraceLoopSettings\TraceLoopSettings;
use PhpProfiler\Lib\PhpProcessReader\PhpGlobalsFinder;
use PhpProfiler\Lib\PhpProcessReader\PhpMemoryReader\ExecutorGlobalsReader;

final class PhpReaderTraceLoop implements PhpReaderTraceLoopInterface
{
    private PhpGlobalsFinder $php_globals_finder;
    private ExecutorGlobalsReader $executor_globals_reader;
    private ReaderLoopProvider $reader_loop_provider;

    public function __construct(
        PhpGlobalsFinder $php_globals_finder,
        ExecutorGlobalsReader $executor_globals_reader,
        ReaderLoopProvider $reader_loop_provider
    ) {
        $this->php_globals_finder = $php_globals_finder;
        $this->executor_globals_reader = $executor_globals_reader;
        $this->reader_loop_provider = $reader_loop_provider;
    }

    /**
     * @param TargetProcessSettings $target_process_settings
     * @param TraceLoopSettings $loop_settings
     * @param TargetPhpSettings $target_php_settings
     * @param GetTraceSettings $get_trace_settings
     * @return Generator<TraceMessage>
     * @throws \PhpProfiler\Lib\Elf\Parser\ElfParserException
     * @throws \PhpProfiler\Lib\Elf\Process\ProcessSymbolReaderException
     * @throws \PhpProfiler\Lib\Elf\Tls\TlsFinderException
     * @throws \PhpProfiler\Lib\Process\MemoryReader\MemoryReaderException
     */
    public function run(
        TargetProcessSettings $target_process_settings,
        TraceLoopSettings $loop_settings,
        TargetPhpSettings $target_php_settings,
        GetTraceSettings $get_trace_settings
    ): Generator {
        $eg_address = $this->php_globals_finder->findExecutorGlobals($target_process_settings, $target_php_settings);

        $loop = $this->reader_loop_provider->getMainLoop(
            function () use (
                $get_trace_settings,
                $target_process_settings,
                $target_php_settings,
                $eg_address
            ): \Generator {
                $call_trace = $this->executor_globals_reader->readCallTrace(
                    $target_process_settings->pid,
                    $target_php_settings->php_version,
                    $eg_address,
                    $get_trace_settings->depth
                );
                yield new TraceMessage($call_trace);
            },
            $loop_settings
        );
        /** @var Generator<TraceMessage> */
        $loop_process = $loop->invoke();
        yield from $loop_process;
    }
}
