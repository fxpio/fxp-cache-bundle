<?php

/*
 * This file is part of the Sonatra package.
 *
 * (c) François Pluchino <francois.pluchino@sonatra.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonatra\Bundle\CacheBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;

/**
 * Replace the symfony cache adapters by the sonatra cache adapters for all services
 * with the "cache.pool" tag.
 *
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
class CachePoolPass implements CompilerPassInterface
{
    /**
     * @var string
     */
    private static $stBase = 'Sonatra\Component\Cache\Adapter\\';

    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        $availables = $this->getAvailableServices($container);

        foreach ($container->findTaggedServiceIds('cache.pool') as $id => $attributes) {
            $def = $container->getDefinition($id);
            $name = basename($def->getClass());

            if ($this->endsWith($name, 'Adapter')) {
                $class = self::$stBase.$name;

                if (class_exists($class) && (empty($availables) || in_array($id, $availables))) {
                    $def->setClass($class);
                }
            }
        }
    }

    /**
     * Get the availables service ids.
     *
     * @param ContainerBuilder $container The container
     *
     * @return string[]
     */
    private function getAvailableServices(ContainerBuilder $container)
    {
        $availables = $container->getParameter('sonatra_cache.override_cache_services');

        $container->getParameterBag()->remove('sonatra_cache.override_cache_services');

        return $availables;
    }

    /**
     * Check if the string ends with.
     *
     * @param string $haystack The haystack
     * @param string $needle   The needle
     *
     * @return bool
     */
    protected function endsWith($haystack, $needle)
    {
        $length = strlen($needle);

        return $length > 0 && (substr($haystack, -$length) === $needle);
    }
}