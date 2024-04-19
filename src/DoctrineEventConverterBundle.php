<?php

namespace DualMedia\DoctrineEventConverterBundle;

use DualMedia\DoctrineEventConverterBundle\DependencyInjection\CompilerPass\EventDetectionCompilerPass;
use DualMedia\DoctrineEventConverterBundle\Proxy\Generator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class DoctrineEventConverterBundle extends Bundle
{
    public const CACHE_DIRECTORY = 'dm-smd-event-distributor-bundle';

    public const CONFIGURATION_ROOT = 'doctrine_event_converter';

    /**
     * @var callable|null
     */
    private $autoloader;

    public function build(
        ContainerBuilder $container
    ): void {
        $container->addCompilerPass(new EventDetectionCompilerPass());
    }

    public function boot(): void
    {
        $this->autoloader = function ($class) {
            if (0 !== mb_strpos($class, Generator::PROXY_NS)) {
                return;
            }

            /** @var Generator $generator */
            $generator = $this->container?->get(Generator::class);
            $file = $generator->resolveFilePath($class);

            if (file_exists($file)) {
                require $file;
            }
        };

        spl_autoload_register($this->autoloader);
    }

    public function shutdown(): void
    {
        if (null !== $this->autoloader) {
            spl_autoload_unregister($this->autoloader);
            $this->autoloader = null;
        }
    }
}
