<?php

namespace Digidirect\StoreLocator\Observer\Adminhtml;

use Digidirect\StoreLocator\Component\Serialize;
use Digidirect\StoreLocator\Helper\Config;
use Magento\Eav\Model\Entity\Attribute\Set;
use Magento\Framework\Event\ObserverInterface;
use Magento\Eav\Model\Entity\TypeFactory;
use Digidirect\AbstractEntity\Model\AttributeSetRepository;
use Magento\Config\Model\ResourceModel\Config as ConfigResource;
use Magento\Framework\App\Cache\TypeListInterface;

/**
 * Save all first renames of entity
 * Invalidate cache if requires
 */
class AttributeSetSaveBefore implements ObserverInterface
{
    /**
     * @var AttributeSetRepository
     */
    protected $repository;

    /**
     * @var ConfigResource
     */
    protected $config;

    /**
     * @var Config
     */
    protected $helper;

    /**
     * @var Serialize
     */
    protected $serialize;

    /**
     * @var TypeListInterface
     */
    protected $typeList;

    /**
     * AttributeSetSaveBefore constructor.
     * @param AttributeSetRepository $setRepository
     * @param ConfigResource $config
     * @param Config $configHelper
     * @param Serialize $serialize
     * @param TypeListInterface $typeList
     */
    public function __construct(
        AttributeSetRepository $setRepository,
        ConfigResource $config,
        Config $configHelper,
        Serialize $serialize,
        TypeListInterface $typeList
    ) {
        $this->typeList = $typeList;
        $this->serialize = $serialize;
        $this->helper = $configHelper;
        $this->config = $config;
        $this->repository = $setRepository;
    }

    /**
     * @param \Magento\Framework\Event\Observer $observer
     * @return $this
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        /**
         * @var $model Set
         */
        $model = $observer->getEvent()->getDataObject();
        if (!($asId = $model->getId())) {
            return $this;
        }

        try {
            $attributeSet = $this->repository->get($asId);

            $connection = $this->config->getConnection();
            $currentDb = $connection->select()
                ->from($this->config->getTable('core_config_data'), ['value'])
                ->where('scope_id = ?', 0)
                ->where('path = ?', Config::XML_PAT_NAME_HISTORY)
                ->where('scope = ? ', 'default');
            $currentValue = $this->helper->getHistory($connection->fetchOne($currentDb));

            if (!is_array($currentValue)) {
                $currentValue = [];
            }
            $name = $this->normalizeName($attributeSet->getAttributeSetName());
            $initialRename = $currentValue[$name] ?? null;
            if (!$initialRename) {
                $currentValue[$name] = $attributeSet->getAttributeSetId();
                $this->typeList->invalidate(\Magento\Framework\App\Cache\Type\Config::TYPE_IDENTIFIER);
            }
            $this->config->saveConfig(
                Config::XML_PAT_NAME_HISTORY,
                $this->serialize->serialize($currentValue),
                'default',
                0
            );
            return $this;

        } catch (\Throwable $exception) {
            return $this;
        }
    }

    /**
     * @param string $name
     * @return string
     */
    protected function normalizeName($name)
    {
        if(empty($name))
        {
            return $name;
        }
        return trim(strtolower($name));
    }
}
