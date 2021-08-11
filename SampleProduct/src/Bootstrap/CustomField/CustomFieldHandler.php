<?php declare(strict_types=1);

namespace SampleProduct\Bootstrap\CustomField;

use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\Framework\Uuid\Uuid;
use Shopware\Core\System\CustomField\Aggregate\CustomFieldSet\CustomFieldSetEntity;
use Shopware\Core\System\CustomField\CustomFieldEntity;
use Symfony\Component\Serializer\Encoder\XmlEncoder;

class CustomFieldHandler
{
    /**
     * @var EntityRepositoryInterface
     */
    private $customFieldSetRepository;

    /**
     * @var EntityRepositoryInterface
     */
    private $customFieldRepository;

    public function __construct(
        EntityRepositoryInterface $customFieldSetRepository,
        EntityRepositoryInterface $customFieldRepository
    ) {
        $this->customFieldSetRepository = $customFieldSetRepository;
        $this->customFieldRepository = $customFieldRepository;
    }

    private function getCustomFieldConfig(): array
    {
        $xmlFiles = glob(__DIR__ . '/data_files/*');
        $xmlEncoder = new XmlEncoder();
        $dataArray = [];

        foreach ($xmlFiles as $xmlFile) {
            $dataArrayOfFile = $xmlEncoder->decode(file_get_contents($xmlFile), 'xml');

            //convert to array if xml file only contains one custom field set
            $customFieldSetsFromFile = empty($dataArrayOfFile['custom_field_set'][0]) ? [$dataArrayOfFile['custom_field_set']] : $dataArrayOfFile['custom_field_set'];

            foreach ($customFieldSetsFromFile as &$customFieldSet) {
                $customFieldSet['customFields'] = empty($customFieldSet['customFields']['customField'][0]) ? [$customFieldSet['customFields']['customField']] : $customFieldSet['customFields']['customField'];
                $customFieldSet['relations'] = empty($customFieldSet['relations']['relation'][0]) ? [$customFieldSet['relations']['relation']] : $customFieldSet['relations']['relation'];

                foreach ($customFieldSet['customFields'] as &$customField){

                    if(array_key_exists('options', $customField['config'])){
                        if (isset($customField['config']['options']['option'])) {
                            $customField['config']['options'] = $customField['config']['options']['option'];
                        }
                    }
                }

                $dataArray[] = $customFieldSet;
            }
        }

        return $dataArray;
    }

    public function addIfNotExists(): void
    {
        $this->removeOld();

        foreach ($this->getCustomFieldConfig() as $config) {
            $customFieldSet = $this->getCustomFieldSet($config['name']);

            if (!$customFieldSet) {
                $this->createCustomFieldset($config);
            }

            foreach ($config['customFields'] as $field) {
                if (!$this->getCustomField($config['name'], $field['name'])) {
                    $this->createCustomField($config['name'], $field);
                }
            }
        }
    }

    public function removeIfExists(): void
    {
        $this->removeOld();

        foreach ($this->getCustomFieldConfig() as $config) {
            foreach ($config['customFields'] as $field) {
                $customField = $this->getCustomField($config['name'], $field['name']);

                if ($customField) {
                    $this->removeCustomField($customField->getId());
                }
            }

            $customFieldSet = $this->getCustomFieldSet($config['name']);

            if ($customFieldSet) {
                $this->removeCustomFieldset($customFieldSet->getId());
            }
        }
    }

    private function getCustomFieldSet($name): ?CustomFieldSetEntity
    {
        if (!$name) {
            return null;
        }

        $criteria = (new Criteria())
            ->addFilter(new EqualsFilter('name', $name));

        $result = $this->customFieldSetRepository->search($criteria, Context::createDefaultContext());

        return $result->first();
    }

    private function createCustomFieldset($customFieldSet): void
    {
        if (
            !$customFieldSet['name'] ||
            !$customFieldSet['config'] ||
            !$customFieldSet['relations']
        ) {
            return;
        }

        $this->customFieldSetRepository->create([
            [
                'id' => Uuid::randomHex(),
                'name' => $customFieldSet['name'],
                'config' => $customFieldSet['config'],
                'relations' => $customFieldSet['relations']
            ]
        ], Context::createDefaultContext());
    }

    private function getCustomField($setName, $fieldName): ?CustomFieldEntity
    {
        if (!$setName || !$fieldName) {
            return null;
        }

        $customFieldSet = $this->getCustomFieldSet($setName);

        if (!$customFieldSet) {
            return null;
        }

        $criteria = (new Criteria())
            ->addFilter(new EqualsFilter('name', $fieldName))
            ->addFilter(new EqualsFilter('customFieldSetId', $customFieldSet->getId()));

        return $this->customFieldRepository
            ->search($criteria, Context::createDefaultContext())
            ->first();
    }

    private function createCustomField($setName, $customField): void
    {
        if (
            !$customField['name'] ||
            !$customField['config']
        ) {
            return;
        }

        $customFieldSet = $this->getCustomFieldSet($setName);

        if (!$customFieldSet) {
            return;
        }

        $this->customFieldRepository->create([
            [
                'id' => Uuid::randomHex(),
                'name' => $customField['name'],
                'type' => $customField['type'],
                'config' => $customField['config'],
                'customFieldSetId' => $customFieldSet->getId()
            ]
        ], Context::createDefaultContext());
    }

    private function removeCustomField($id): void
    {
        $this->customFieldRepository->delete([['id' => $id]], Context::createDefaultContext());
    }

    private function removeCustomFieldset($id): void
    {
        $this->customFieldSetRepository->delete([['id' => $id]], Context::createDefaultContext());
    }

    private function removeOld(): void
    {
        $customConfig = [];

        foreach ($customConfig as $customSetName => $customFields) {
            foreach ($customFields as $field) {
                $customField = $this->getCustomField($customSetName, $field);

                if ($customField) {
                    $this->removeCustomField($customField->getId());
                }
            }
            $customFieldSet = $this->getCustomFieldSet($customSetName);

            if ($customFieldSet) {
                $this->removeCustomFieldset($customFieldSet->getId());
            }
        }
    }
}
