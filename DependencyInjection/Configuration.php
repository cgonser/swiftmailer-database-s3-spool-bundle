<?php

namespace Cgonser\SwiftMailerDatabaseS3SpoolBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * This is the class that validates and merges configuration from your app/config files.
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/configuration.html}
 */
class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritdoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('cgonser_swift_mailer_database_s3_spool');

        $rootNode
            ->children()
                ->scalarNode("entity_class")
                    ->defaultValue('Cgonser\SwiftMailerDatabaseS3SpoolBundle\Entity\MailQueue')
                    ->cannotBeEmpty()
                    ->end()
                ->scalarNode("s3_bucket")
                    ->isRequired()
                    ->end()
            ->end()
        ;

        return $treeBuilder;
    }
}
