<?php

/*
 * This file is part of the Search PHP Bundle.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * Feel free to edit as you please, and have fun.
 *
 * @author Marc Morera <yuhu@mmoreram.com>
 * @author PuntMig Technologies
 */

declare(strict_types=1);

namespace Apisearch\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

/**
 * Class ApisearchExtension.
 */
class ApisearchExtension extends Extension implements PrependExtensionInterface
{
    /**
     * Loads a specific configuration.
     *
     * @throws \InvalidArgumentException When provided tag is not defined in this extension
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $loader = new YamlFileLoader(
            $container,
            new FileLocator(__DIR__.'/../Resources/config')
        );

        $confiFiles = [
            'commands',
            'repositories',
            'url',
            'twig',
            'http',
            'transformers',
            'exporters',
            'translator',
        ];

        foreach ($confiFiles as $configFile) {
            $loader->load("$configFile.yml");
        }
    }

    /**
     * Allow an extension to prepend the extension configurations.
     *
     * @param ContainerBuilder $container
     */
    public function prepend(ContainerBuilder $container)
    {
        $configurationInstance = new ApisearchConfiguration();
        $configuration = $container->getExtensionConfig('apisearch');
        $configuration = $this->processConfiguration(
            $configurationInstance,
            $configuration
        );
        $configuration = $container
            ->getParameterBag()
            ->resolveValue($configuration);

        $container
            ->getParameterBag()
            ->add([
                'apisearch.repository_configuration' => $configuration['repositories'],
            ]);
    }
}
