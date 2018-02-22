<?php

namespace Silverback\ApiComponentBundle;

use Doctrine\Bundle\DoctrineBundle\DependencyInjection\Compiler\DoctrineOrmMappingsPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

// use Doctrine\Bundle\CouchDBBundle\DependencyInjection\Compiler\DoctrineCouchDBMappingsPass;
// use Doctrine\Bundle\MongoDBBundle\DependencyInjection\Compiler\DoctrineMongoDBMappingsPass;

class SilverbackApiComponentBundle extends Bundle
{
    /**
     * @param ContainerBuilder $container
     */
    public function build(ContainerBuilder $container): void
    {
        parent::build($container);
        $this->addRegisterMappingsPass($container);
    }

    /**
     * @param ContainerBuilder $container
     */
    private function addRegisterMappingsPass(ContainerBuilder $container): void
    {
        /* $mappings = array(
            realpath(__DIR__.'/Resources/config/doctrine-mapping') => __NAMESPACE__ . '\\Entity',
        ); */
        if (class_exists(DoctrineOrmMappingsPass::class)) {
            // $container->addCompilerPass(DoctrineOrmMappingsPass::createXmlMappingDriver($mappings));
            // Opted for annotations to support traits
            $container->addCompilerPass(DoctrineOrmMappingsPass::createAnnotationMappingDriver([ __NAMESPACE__ . '\\Entity'], [__DIR__ . '/Entity']));
        }
        /* if (class_exists(DoctrineMongoDBMappingsPass::class)) {
            $container->addCompilerPass(DoctrineMongoDBMappingsPass::createXmlMappingDriver($mappings));
        }
        if (class_exists(DoctrineCouchDBMappingsPass::class)) {
            $container->addCompilerPass(DoctrineCouchDBMappingsPass::createXmlMappingDriver($mappings));
        } */
    }
}
