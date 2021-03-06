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

namespace PhpProfiler\Inspector\Daemon\Reader\Controller;

use Amp\Promise;
use PhpProfiler\Inspector\Daemon\Reader\Protocol\Message\DetachWorkerMessage;
use PhpProfiler\Inspector\Daemon\Reader\Protocol\Message\TraceMessage;
use PhpProfiler\Inspector\Daemon\Reader\Protocol\Message\AttachMessage;
use PhpProfiler\Inspector\Daemon\Reader\Protocol\Message\SetSettingsMessage;
use PhpProfiler\Inspector\Daemon\Reader\Protocol\PhpReaderControllerProtocolInterface;
use PhpProfiler\Inspector\Settings\GetTraceSettings\GetTraceSettings;
use PhpProfiler\Inspector\Settings\TargetPhpSettings\TargetPhpSettings;
use PhpProfiler\Inspector\Settings\TraceLoopSettings\TraceLoopSettings;
use PhpProfiler\Lib\Amphp\ContextInterface;

final class PhpReaderController implements PhpReaderControllerInterface
{
    /** @var ContextInterface<PhpReaderControllerProtocolInterface> */
    private ContextInterface $context;

    /**
     * PhpReaderContext constructor.
     * @param ContextInterface<PhpReaderControllerProtocolInterface> $context
     */
    public function __construct(ContextInterface $context)
    {
        $this->context = $context;
    }

    public function start(): Promise
    {
        return $this->context->start();
    }

    public function isRunning(): bool
    {
        return $this->context->isRunning();
    }

    /**
     * @param TargetPhpSettings $target_php_settings
     * @param TraceLoopSettings $loop_settings
     * @param GetTraceSettings $get_trace_settings
     * @return Promise<int>
     */
    public function sendSettings(
        TargetPhpSettings $target_php_settings,
        TraceLoopSettings $loop_settings,
        GetTraceSettings $get_trace_settings
    ): Promise {
        /** @var Promise<int> */
        return $this->context->getProtocol()->sendSettings(
            new SetSettingsMessage(
                $target_php_settings,
                $loop_settings,
                $get_trace_settings
            )
        );
    }

    /**
     * @param int $pid
     * @return Promise<int>
     */
    public function sendAttach(int $pid): Promise
    {
        /** @var Promise<int> */
        return $this->context->getProtocol()->sendAttach(
            new AttachMessage($pid)
        );
    }

    /**
     * @return Promise<TraceMessage|DetachWorkerMessage>
     */
    public function receiveTraceOrDetachWorker(): Promise
    {
        /** @var Promise<TraceMessage|DetachWorkerMessage> */
        return $this->context->getProtocol()->receiveTraceOrDetachWorker();
    }
}
