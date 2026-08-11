<?php

declare(strict_types=1);

namespace ExAkt\CtmHeader\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

/**
 * Ohne diese Extension laedt Symfony die config/services.yaml des Bundles nicht
 * und der Controller wird nie als Fragment registriert – das Modul fehlt dann
 * kommentarlos in der Typ-Auswahl von tl_module.
 */
class ExAktCtmHeaderExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container): void
    {
        $loader = new YamlFileLoader(
            $container,
            new FileLocator(\dirname(__DIR__, 2).'/config'),
        );

        $loader->load('services.yaml');
    }
}
