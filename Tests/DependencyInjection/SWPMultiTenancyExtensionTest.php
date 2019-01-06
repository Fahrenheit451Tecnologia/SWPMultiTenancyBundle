<?php

/*
 * This file is part of the Superdesk Web Publisher MultiTenancy Bundle.
 *
 * Copyright 2016 Sourcefabric z.u. and contributors.
 *
 * For the full copyright and license information, please see the
 * AUTHORS and LICENSE files distributed with this source code.
 *
 * @copyright 2016 Sourcefabric z.ú
 * @license http://www.superdesk.org/license
 */

namespace SWP\Bundle\MultiTenancyBundle\Tests\DependencyInjection;

use Matthias\SymfonyDependencyInjectionTest\PhpUnit\AbstractExtensionTestCase;
use SWP\Bundle\MultiTenancyBundle\DependencyInjection\SWPMultiTenancyExtension;
use SWP\Bundle\MultiTenancyBundle\Doctrine\ORM\TenantRepository;
use SWP\Component\MultiTenancy\Factory\TenantFactory;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBag;

class SWPMultiTenancyExtensionTest extends AbstractExtensionTestCase
{
    /**
     * @covers \SWP\Bundle\MultiTenancyBundle\SWPMultiTenancyBundle
     * @covers \SWP\Bundle\MultiTenancyBundle\DependencyInjection\SWPMultiTenancyExtension::load
     * @covers \SWP\Bundle\MultiTenancyBundle\DependencyInjection\Configuration::getConfigTreeBuilder
     */
    public function testLoad()
    {
        $container = $this->createContainer();
        $loader = $this->createLoader();
        $config = $this->getConfig();

        $loader->load([$config], $container);

        $this->assertTrue($container->getParameter('swp_multi_tenancy.backend_type_orm'));

        $this->assertEquals(
            'SWP\Component\MultiTenancy\Model\Tenant',
            $container->getParameter('swp.model.tenant.class')
        );

        $this->assertEquals(
            'SWP\Component\MultiTenancy\Factory\TenantFactory',
            $container->getParameter('swp.factory.tenant.class')
        );
    }

    protected function createLoader()
    {
        return new SWPMultiTenancyExtension();
    }

    protected function createContainer(array $data = [])
    {
        return new ContainerBuilder(new ParameterBag($data));
    }

    protected function getConfig()
    {
        return [
            'persistence'       => [
                'orm'           => [
                    'enabled'   => true,
                ]
            ]
        ];
    }

    /**
     * @test
     */
    public function if_orm_backend_is_enabled()
    {
        $this->load(['persistence' => ['orm' => ['enabled' => true]]]);

        $this->assertContainerBuilderHasParameter('swp_multi_tenancy.backend_type_orm', true);
    }

    /**
     * @test
     * @expectedException \Symfony\Component\Config\Definition\Exception\InvalidConfigurationException
     */
    public function if_persistence_backend_is_not_enabled()
    {
        $this->load();
    }

    /**
     * @test
     */
    public function the_orm_listeners_are_disabled_by_default()
    {
        $this->load(['persistence' => ['orm' => ['enabled' => true]]]);

        $this->assertContainerBuilderNotHasService('swp_multi_tenancy.tenant_listener');
        $this->assertContainerBuilderNotHasService('swp_multi_tenancy.tenant_subscriber');
    }

    /**
     * @test
     */
    public function the_orm_listeners_are_enabled()
    {
        $this->load(['use_orm_listeners' => true, 'persistence' => ['orm' => ['enabled' => true]]]);

        $this->assertContainerBuilderHasService('swp_multi_tenancy.tenant_listener');
        $this->assertContainerBuilderHasService('swp_multi_tenancy.tenant_subscriber');
    }

    /**
     * @test
     */
    public function if_loads_all_needed_services_by_default()
    {
        $this->load(['persistence' => ['orm' => ['enabled' => true]]]);

        $this->assertContainerBuilderHasService('swp.repository.tenant', TenantRepository::class);
        $this->assertContainerBuilderHasService('swp.factory.tenant', TenantFactory::class);
        $this->assertContainerBuilderHasService('swp.object_manager.tenant');
    }

    /**
     * @test
     */
    public function when_phpcr_backend_enabeled()
    {
        $this->load(['persistence' => ['orm' => ['enabled' => true]]]);

        $this->assertContainerBuilderHasService('swp.repository.tenant', TenantRepository::class);
        $this->assertContainerBuilderHasService('swp.factory.tenant', TenantFactory::class);
        $this->assertContainerBuilderHasService('swp.object_manager.tenant');
    }

    /**
     * {@inheritdoc}
     */
    protected function getContainerExtensions()
    {
        return [
            new SWPMultiTenancyExtension(),
        ];
    }
}
