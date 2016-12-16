<?php

/*
 * This file is part of the Sonatra package.
 *
 * (c) François Pluchino <francois.pluchino@sonatra.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonatra\Bundle\CacheBundle\Tests\DependencyInjection;

use Sonatra\Bundle\CacheBundle\DependencyInjection\Compiler\CachePoolPass;
use Sonatra\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Component\Cache\Adapter\AdapterInterface;
use Symfony\Component\Cache\Adapter\ApcuAdapter as SymfonyApcuAdapter;
use Symfony\Component\Cache\Adapter\FilesystemAdapter as SymfonyFilesystemAdapter;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;

/**
 * Cache Pool Pass Tests.
 *
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
class CachePoolPassTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ContainerBuilder
     */
    protected $container;

    /**
     * @var CachePoolPass
     */
    protected $compiler;

    protected function setUp()
    {
        $this->container = new ContainerBuilder();
        $this->compiler = new CachePoolPass();
    }

    public function testOverrideCacheAdapterServiceClasses()
    {
        /* @var Definition[] $poolDefinitions */
        $poolDefinitions = array(
            'cache.adapter.filesystem' => $this->createCacheDefinition(SymfonyFilesystemAdapter::class),
            'cache.adapter.apcu' => $this->createCacheDefinition(SymfonyApcuAdapter::class),
            'cache.adapter.abstract_adapter' => $this->createCacheDefinition(AdapterInterface::class),
            '' => $this->createCacheDefinition(AdapterInterface::class),
        );

        $this->container->addDefinitions($poolDefinitions);
        $this->container->setParameter('sonatra_cache.override_cache_services', array(
            'cache.adapter.filesystem',
        ));

        $this->compiler->process($this->container);

        $this->assertSame(FilesystemAdapter::class, $poolDefinitions['cache.adapter.filesystem']->getClass());
        $this->assertSame(SymfonyApcuAdapter::class, $poolDefinitions['cache.adapter.apcu']->getClass());
        $this->assertSame(AdapterInterface::class, $poolDefinitions['cache.adapter.abstract_adapter']->getClass());
    }

    private function createCacheDefinition($class)
    {
        $def = new Definition($class);
        $def->addTag('cache.pool');

        return $def;
    }
}