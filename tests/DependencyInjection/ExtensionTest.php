<?php

namespace DM\DoctrineEventDistributorBundle\Tests\DependencyInjection;

use DM\DoctrineEventDistributorBundle\DependencyInjection\DoctrineEventConverterExtension;
use DM\DoctrineEventDistributorBundle\DoctrineEventConverterBundle;
use Matthias\SymfonyDependencyInjectionTest\PhpUnit\AbstractExtensionTestCase;

class ExtensionTest extends AbstractExtensionTestCase
{
    protected function getContainerExtensions(): array
    {
        return [
            new DoctrineEventConverterExtension(),
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
            DoctrineEventConverterBundle::CONFIGURATION_ROOT.'.parent_directory',
            '%kernel.project_dir%/src/*'
        );

        $this->assertContainerBuilderHasParameter(
            DoctrineEventConverterBundle::CONFIGURATION_ROOT.'.parent_namespace',
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
            DoctrineEventConverterBundle::CONFIGURATION_ROOT.'.parent_directory',
            __DIR__
        );

        $this->assertContainerBuilderHasParameter(
            DoctrineEventConverterBundle::CONFIGURATION_ROOT.'.parent_namespace',
            'TestNamespace'
        );
    }
}
