<?php

namespace DM\DoctrineEventDistributorBundle;

use DM\DoctrineEventDistributorBundle\DependencyInjection\CompilerPass\EventDetectionCompilerPass;
use DM\DoctrineEventDistributorBundle\Proxy\Generator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class EventDistributorBundle extends Bundle
{
    public const CACHE_DIRECTORY = 'dm-smd-event-distributor-bundle';

    /**
     * @var callable|null
     */
    private $autoloader = null;

    public function build(
        ContainerBuilder $container
    ) {
        $container->addCompilerPass(new EventDetectionCompilerPass());
    }

    public function boot(): void
    {
        $this->autoloader = function ($class) {
            if (0 !== mb_strpos($class, Generator::PROXY_NS)) {
                return;
            }

            /** @var Generator $generator */
            $generator = $this->container->get(Generator::class);
            $file = $generator->resolveFilePath($class);

            if (file_exists($file)) {
                require $file;
            }
        };

        spl_autoload_register($this->autoloader);
    }

    public function shutdown()
    {
        if (null !== $this->autoloader) {
            spl_autoload_unregister($this->autoloader);
            $this->autoloader = null;
        }
    }
}
