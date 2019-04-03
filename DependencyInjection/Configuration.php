<?php

namespace Frcho\Bundle\BackupManagerBundle\DependencyInjection;

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
        $treeBuilder = new TreeBuilder('frcho_backup_manager');
        if (method_exists($treeBuilder, 'getRootNode')) {
            $rootNode = $treeBuilder->getRootNode();
        } else {
            // BC layer for symfony/config 4.1 and older
            $rootNode = $treeBuilder->root('frcho_backup_manager');
        }

        $rootNode
            ->children()
                ->arrayNode('storage')->isRequired()
                    ->children()
                        ->arrayNode('local')
                            ->children()
                                ->scalarNode('type')->end()
                                ->scalarNode('root')->end()
                            ->end()
                        ->end()
                        ->arrayNode('s3')
                            ->children()
                                ->scalarNode('type')->end()
                                ->scalarNode('key')->end()
                                ->scalarNode('secret')->end()
                                ->scalarNode('region')->end()
                                ->scalarNode('version')->end()
                                ->scalarNode('bucket')->end()
                                ->scalarNode('root')->end()
                            ->end()
                        ->end()
                        ->arrayNode('rackspace')
                            ->children()
                                ->scalarNode('type')->end()
                                ->scalarNode('username')->end()
                                ->scalarNode('password')->end()
                                ->scalarNode('container')->end()
                            ->end()
                        ->end()
                        ->arrayNode('dropbox')
                            ->children()
                                ->scalarNode('type')->end()
                                ->scalarNode('token')->end()
                                ->scalarNode('key')->end()
                                ->scalarNode('secret')->end()
                                ->scalarNode('app')->end()
                                ->scalarNode('root')->end()
                            ->end()
                        ->end()
                        ->arrayNode('ftp')
                            ->children()
                                ->scalarNode('type')->end()
                                ->scalarNode('host')->end()
                                ->scalarNode('username')->end()
                                ->scalarNode('password')->end()
                                ->scalarNode('root')->end()
                                ->scalarNode('port')->end()
                                ->scalarNode('passive')->end()
                                ->scalarNode('ssl')->end()
                                ->scalarNode('timeout')->end()
                            ->end()
                        ->end()
                        ->arrayNode('sftp')
                            ->children()
                                ->scalarNode('type')->end()
                                ->scalarNode('host')->end()
                                ->scalarNode('username')->end()
                                ->scalarNode('password')->end()
                                ->scalarNode('root')->end()
                                ->scalarNode('port')->end()
                                ->scalarNode('timeout')->end()
                                ->scalarNode('privateKey')->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
                ->arrayNode('database')->isRequired()
                    ->prototype('array')
                        ->children()
                            ->scalarNode('type')->end()
                            ->scalarNode('host')->end()
                            ->scalarNode('port')->end()
                            ->scalarNode('user')->end()
                            ->scalarNode('pass')->end()
                            ->scalarNode('database')->end()
                        ->end()
                    ->end()
                ->end()
            ->end()
        ;

        return $treeBuilder;
    }
}
