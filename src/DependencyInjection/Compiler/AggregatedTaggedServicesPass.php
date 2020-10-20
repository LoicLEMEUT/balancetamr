<?php

namespace App\DependencyInjection\Compiler;

use App\Services\Gitlab\GitlabService;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Class AggregatedTaggedServicesPass
 *
 * @package Actualys\Bundle\DrupalCommerceConnectorBundle\DependencyInjection\Compiler
 */
class AggregatedTaggedServicesPass implements CompilerPassInterface
{

    /**
     * @see \Symfony\Component\DependencyInjection\Compiler.CompilerPassInterface::process()
     */
    public function process(ContainerBuilder $container)
    {
        if (!$container->has(GitlabService::class)) {
            return;
        }

        // always first check if the primary service is defined
        $definition = $container->findDefinition(GitlabService::class);

        // find all service IDs with the app.mail_transport tag
        $taggedServices = $container->findTaggedServiceIds('app.gitlab_providers');

        foreach ($taggedServices as $id => $tags) {
            // a service could have the same tag twice
            foreach ($tags as $attributes) {
                $definition->addMethodCall('addProvider', [new Reference($id), $attributes['alias']]);
            }
        }
    }
}
