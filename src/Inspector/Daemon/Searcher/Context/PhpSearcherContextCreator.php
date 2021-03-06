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

namespace PhpProfiler\Inspector\Daemon\Searcher\Context;

use PhpProfiler\Inspector\Daemon\Searcher\Controller\PhpSearcherController;
use PhpProfiler\Inspector\Daemon\Searcher\Controller\PhpSearcherControllerInterface;
use PhpProfiler\Inspector\Daemon\Searcher\Controller\PhpSearcherControllerProtocol;
use PhpProfiler\Inspector\Daemon\Searcher\Worker\PhpSearcherWorkerProtocol;
use PhpProfiler\Inspector\Daemon\Searcher\Worker\PhpSearcherEntryPoint;
use PhpProfiler\Lib\Amphp\ContextCreatorInterface;

final class PhpSearcherContextCreator
{
    private ContextCreatorInterface $context_creator;

    public function __construct(ContextCreatorInterface $context_creator)
    {
        $this->context_creator = $context_creator;
    }

    public function create(): PhpSearcherControllerInterface
    {
        return new PhpSearcherController(
            $this->context_creator->create(
                PhpSearcherEntryPoint::class,
                PhpSearcherWorkerProtocol::class,
                PhpSearcherControllerProtocol::class
            )
        );
    }
}
