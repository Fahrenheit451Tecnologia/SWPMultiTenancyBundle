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

namespace SWP\Bundle\MultiTenancyBundle\EventListener;

use Doctrine\ORM\EntityManagerInterface;
use SWP\Component\MultiTenancy\Context\TenantContextInterface;
use SWP\Component\MultiTenancy\Repository\TenantRepositoryInterface;
use Symfony\Bridge\Doctrine\RegistryInterface;
use Symfony\Component\Console\Event\ConsoleCommandEvent;

/**
 * Class ConsoleCommandTenantListener.
 *
 * It set tenant from tenant code provided in console command options
 */
class ConsoleCommandTenantListener
{
    /**
     * @var RegistryInterface
     */
    protected $doctrine;

    /**
     * @var TenantContextInterface
     */
    protected $tenantContext;

    /**
     * @var TenantRepositoryInterface
     */
    protected $tenantRepository;

    /**
     * @var EntityManagerInterface
     */
    protected $entityManager;

    /**
     * @param RegistryInterface $doctrine
     * @param TenantContextInterface $tenantContext
     * @param TenantRepositoryInterface $tenantRepository
     */
    public function __construct(
        RegistryInterface $doctrine,
        TenantContextInterface $tenantContext,
        TenantRepositoryInterface $tenantRepository
    ) {
        $this->doctrine = $doctrine;
        $this->tenantContext = $tenantContext;
        $this->tenantRepository = $tenantRepository;
    }

    /**
     * @param ConsoleCommandEvent $event
     */
    public function onConsoleCommand(ConsoleCommandEvent $event)
    {
        if (!$event->getInput()->hasOption('tenant')) {
            return;
        }

        if (null === $tenantCode = $event->getInput()->getOption('tenant')) {
            return;
        }

        if (null === $tenant = $this->tenantRepository->findOneByCode($tenantCode)) {
            throw new \RuntimeException(sprintf('Tenant with code %s was not found', $tenantCode));
        }

        $this->tenantContext->setTenant($tenant);
        /** @var EntityManagerInterface $entityManager */
        $entityManager = $this->doctrine->getManager();
        $entityManager
            ->getFilters()
            ->enable('tenantable')
            ->setParameter('tenantCode', $tenant->getCode());
    }
}
