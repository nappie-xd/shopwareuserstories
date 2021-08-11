<?php declare(strict_types=1);

namespace LegendaryHeaderThemeV2;

use Shopware\Core\Framework\Plugin;
use Shopware\Storefront\Framework\ThemeInterface;

class LegendaryHeaderThemeV2 extends Plugin implements ThemeInterface
{
    public function getThemeConfigPath(): string
    {
        return 'theme.json';
    }
}