<?php declare(strict_types=1);

namespace ImportantFilters;

use Shopware\Core\Framework\Plugin;
use Shopware\Storefront\Framework\ThemeInterface;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;
use Shopware\Core\Framework\Plugin\Context\InstallContext;
use Shopware\Core\Framework\Plugin\Context\UninstallContext;
use Shopware\Core\Framework\Plugin\Context\UpdateContext;
use ImportantFilters\Bootstrap\CustomField\CustomFieldHandler;

class ImportantFilters extends Plugin
{
    public function install(InstallContext $installContext): void
    {
        parent::install($installContext);

        $this->getCustomFieldHandler()->addIfNotExists();
    }

    public function update(UpdateContext $updateContext): void
    {
        parent::update($updateContext);

        $this->getCustomFieldHandler()->addIfNotExists();
    }

    public function uninstall(UninstallContext $uninstallContext): void
    {
        parent::uninstall($uninstallContext);

        if (!$uninstallContext->keepUserData()) {
            $this->getCustomFieldHandler()->removeIfExists();
        }
    }

    private function getCustomFieldHandler()
    {
        /** @var EntityRepositoryInterface $customFieldSetRepository */
        $customFieldSetRepository = $this->container->get('custom_field_set.repository');

        /** @var EntityRepositoryInterface $customFieldRepository */
        $customFieldRepository = $this->container->get('custom_field.repository');

        return new CustomFieldHandler(
            $customFieldSetRepository,
            $customFieldRepository
        );
    }
}