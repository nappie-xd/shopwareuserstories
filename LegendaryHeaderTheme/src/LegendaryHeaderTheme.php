<?php declare(strict_types=1);

namespace LegendaryHeaderTheme;

use Shopware\Core\Framework\Plugin;
use Shopware\Storefront\Framework\ThemeInterface;

class LegendaryHeaderTheme extends Plugin implements ThemeInterface
{
    public function getThemeConfigPath(): string
    {
        return 'theme.json';
    }
}