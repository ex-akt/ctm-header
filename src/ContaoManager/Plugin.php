<?php

declare(strict_types=1);

namespace ExAkt\CtmHeader\ContaoManager;

use Contao\CoreBundle\ContaoCoreBundle;
use Contao\ManagerPlugin\Bundle\BundlePluginInterface;
use Contao\ManagerPlugin\Bundle\Config\BundleConfig;
use Contao\ManagerPlugin\Bundle\Parser\ParserInterface;
use ContaoThemeManager\Core\ContaoThemeManagerCore;
use ContaoThemeManager\PushNavigation\ContaoThemeManagerPushNavigation;
use ExAkt\CtmHeader\ExAktCtmHeader;

class Plugin implements BundlePluginInterface
{
    /**
     * {@inheritdoc}
     */
    public function getBundles(ParserInterface $parser): array
    {
        return [
            BundleConfig::create(ExAktCtmHeader::class)
                ->setLoadAfter([
                    ContaoCoreBundle::class,
                    ContaoThemeManagerCore::class,
                    ContaoThemeManagerPushNavigation::class,
                ])
                ->setReplace(['ctm-header']),
        ];
    }
}
