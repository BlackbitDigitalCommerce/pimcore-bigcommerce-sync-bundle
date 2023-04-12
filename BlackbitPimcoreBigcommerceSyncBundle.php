<?php

namespace Blackbit\PimcoreBigcommerceSyncBundle;

use Blackbit\PimcoreBigcommerceSyncBundle\Tools\Installer;
use Pimcore\Extension\Bundle\AbstractPimcoreBundle;
use Pimcore\Extension\Bundle\Traits\PackageVersionTrait;

class BlackbitPimcoreBigcommerceSyncBundle extends AbstractPimcoreBundle
{
    use PackageVersionTrait {
        getVersion as protected getComposerVersion;
    }

    public function getInstaller()
    {
        return $this->container->get(Installer::class);
    }

    public function getJsPaths()
    {
        return [
        ];
    }

    public function getVersion(): string
    {
        try {
            return $this->getComposerVersion();
        } catch (\Exception $e) {
            return 'unknown';
        }
    }
}
