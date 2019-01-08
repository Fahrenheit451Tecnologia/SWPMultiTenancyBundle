<?php

/*
 * This file is part of the Superdesk Web Publisher MultiTenancyBundle.
 *
 * Copyright 2016 Sourcefabric z.u. and contributors.
 *
 * For the full copyright and license information, please see the
 * AUTHORS and LICENSE files distributed with this source code.
 *
 * @copyright 2016 Sourcefabric z.ú
 * @license http://www.superdesk.org/license
 */

namespace SWP\Bundle\MultiTenancyBundle\Parser;

use LayerShifter\TLDExtract\Extract;
use LayerShifter\TLDExtract\ResultInterface;
use SWP\Component\MultiTenancy\Resolver\TenantResolverInterface;

class HostParser
{
    /**
     * @param string $host
     *
     * @return string
     */
    public static function extractDomain($host)
    {
        $host = self::sanitizeHost($host);

        if (null === $host || TenantResolverInterface::LOCALHOST === $host) {
            return TenantResolverInterface::LOCALHOST;
        }

        $result = self::extractHost($host);

        // handle case for ***.localhost
        if (TenantResolverInterface::LOCALHOST === $result->getSuffix() &&
            null !== $result->getHostname() &&
            null === $result->getSubdomain()
        ) {
            return $result->getSuffix();
        }

        $domainString = $result->getHostname();
        if (null !== $result->getSuffix()) {
            $domainString = $domainString.'.'.$result->getSuffix();
        }

        return $domainString;
    }

    /**
     * Extracts subdomain from the host.
     *
     * @param string $host Hostname
     *
     * @return string
     */
    public static function extractSubdomain($host)
    {
        $host = self::sanitizeHost($host);
        $result = self::extractHost($host);

        // handle case for ***.localhost
        if (TenantResolverInterface::LOCALHOST === $result->getSuffix() &&
            null !== $result->getHostname() &&
            null === $result->getSubdomain()
        ) {
            return $result->getHostname();
        }

        $subdomain = $result->getSubdomain();
        if (null !== $subdomain) {
            return $subdomain;
        }

        return;
    }

    /**
     * @param string $host
     * @return string
     */
    private static function sanitizeHost($host)
    {
        // remove www prefix from host
        return str_replace('www.', '', $host);
    }

    /**
     * @param $host
     * @return ResultInterface
     */
    private static function extractHost($host)
    {
        $extract = new Extract();

        return $extract->parse($host);
    }
}