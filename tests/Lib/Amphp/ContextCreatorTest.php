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

namespace PhpProfiler\Lib\Amphp;

use PHPUnit\Framework\TestCase;

class ContextCreatorTest extends TestCase
{
    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function testCreate(): void
    {
        $creator = new ContextCreator('di_config');
        $namespace = __NAMESPACE__;
        $class_definition = <<<CLASS_DEFINITION
        namespace {$namespace};

        use Amp\Parallel\Sync\Channel;

        class ContextCreatorTestDummyProtocol implements MessageProtocolInterface {
            public static function createFromChannel(Channel \$channel): self
            {
                return new self();
            }
        }
        CLASS_DEFINITION;
        eval($class_definition);

        $context = $creator->create(
            WorkerEntryPointInterface::class,
            ContextCreatorTestDummyProtocol::class,
            ContextCreatorTestDummyProtocol::class,
        );
        $this->assertInstanceOf(Context::class, $context);
    }
}
