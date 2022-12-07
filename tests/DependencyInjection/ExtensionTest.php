<?php

namespace DM\DoctrineEventDistributorBundle\Tests\DependencyInjection;

use DM\DoctrineEventDistributorBundle\DependencyInjection\EventDistributorExtension;
use Matthias\SymfonyDependencyInjectionTest\PhpUnit\AbstractExtensionTestCase;

class ExtensionTest extends AbstractExtensionTestCase
{
    protected function getContainerExtensions(): array
    {
        return [
            new EventDistributorExtension(),
        ];
    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->container->setParameter('kernel.environment', 'test');
    }

    public function testParameterLoading(): void
    {
        $this->load();

        $this->assertContainerBuilderHasParameter(
            'event_distributor.parent_directory',
            '%kernel.project_dir%/src/*'
        );

        $this->assertContainerBuilderHasParameter(
            'event_distributor.parent_namespace',
            'App'
        );
    }

    public function testOverrides(): void
    {
        $this->load([
            'parent_directory' => __DIR__,
            'parent_namespace' => 'TestNamespace',
        ]);

        $this->assertContainerBuilderHasParameter(
            'event_distributor.parent_directory',
            __DIR__
        );

        $this->assertContainerBuilderHasParameter(
            'event_distributor.parent_namespace',
            'TestNamespace'
        );
    }
}
