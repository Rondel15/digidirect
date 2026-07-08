<?php
namespace Digidirect\AbstractAttributes\Observer\Adminhtml;

use Digidirect\AbstractAttributes\Api\AbstractAttributeRepositoryInterface;
use Digidirect\AbstractAttributes\Api\OptionRepositoryInterface;
use Digidirect\AbstractAttributes\Api\Data\OptionInterface;
use Digidirect\AbstractAttributes\Api\Data\OptionInterfaceFactory;
use Digidirect\AbstractAttributes\Model\ResourceModel\Option as OptionResourceModel;
use Magento\Catalog\Api\ProductAttributeOptionManagementInterface;
use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Store\Model\Store;

class CatalogEntityAttributeSaveOptions implements ObserverInterface
{
    /**
     * @var ProductAttributeOptionManagementInterface
     */
    protected $_productAttributeOptionManagement;

    /**
     * @var OptionRepositoryInterface
     */
    protected $_optionRepository;

    /**
     * @var AbstractAttributeRepositoryInterface
     */
    protected $_abstractAttributeRepository;

    /**
     * @var OptionInterfaceFactory
     */
    protected $_optionInterfaceFactory;

    /**
     * @var OptionResourceModel
     */
    protected $_optionResourceModel;

    /**
     * @param AbstractAttributeRepositoryInterface $abstractAttributeRepository
     * @param OptionRepositoryInterface $optionRepository
     * @param OptionInterfaceFactory $optionInterfaceFactory
     * @param OptionResourceModel $optionResourceModel
     * @param ProductAttributeOptionManagementInterface $productAttributeOptionManagement
     */
    public function __construct(
        AbstractAttributeRepositoryInterface $abstractAttributeRepository,
        OptionRepositoryInterface $optionRepository,
        OptionInterfaceFactory $optionInterfaceFactory,
        OptionResourceModel $optionResourceModel,
        ProductAttributeOptionManagementInterface $productAttributeOptionManagement
    ) {
        $this->_abstractAttributeRepository = $abstractAttributeRepository;
        $this->_optionRepository = $optionRepository;
        $this->_optionInterfaceFactory = $optionInterfaceFactory;
        $this->_optionResourceModel = $optionResourceModel;
        $this->_productAttributeOptionManagement = $productAttributeOptionManagement;
    }

    /**
     * Execute
     * @param EventObserver $observer
     * @throws \Magento\Framework\Exception\LocalizedException
     * @return $this
     */
    public function execute(EventObserver $observer)
    {
        /** @var \Magento\Catalog\Model\ResourceModel\Eav\Attribute $attribute */
        $attribute = $observer->getAttribute();
        if ($attribute->getSkipAaSave()) {
            return $this;
        }

        try {
            $eaaAttribute = $this->_abstractAttributeRepository->getByAttributeId($attribute->getAttributeId());
        } catch (NoSuchEntityException $e) {
            return $this;
        }

        if ($eaaAttribute->getStatus()) {
            $optionsToSave = [];
            $defaultLabels = [];
            $options = $this->_productAttributeOptionManagement->getItems($attribute->getAttributeCode());
            foreach ($options as $option) {
                if ($option->getValue()) {
                    try {
                        $eaaOption = $this->_optionRepository->getByOptionId($option->getValue());
                        $defaultLabels[] = $eaaOption->getDefaultLabel();
                    } catch (NoSuchEntityException $e) {
                        $defaultLabel = $option->getLabel();
                        if (in_array($defaultLabel, $defaultLabels)) {
                            $defaultLabel .= ' ' . $option->getValue();
                        }

                        $optionsToSave[] = [
                            OptionInterface::OPTION_ID => $option->getValue(),
                            OptionInterface::STORE_ID => Store::DEFAULT_STORE_ID,
                            OptionInterface::STATUS => OptionInterface::STATUS_ENABLED,
                            OptionInterface::LISTING => 1,
                            OptionInterface::URL_KEY => $this->_optionRepository->cleanUrlKey($defaultLabel),
                        ];
                        $defaultLabels[] = $defaultLabel;
                    }
                }
            }

            if (!empty($optionsToSave)) {
                $this->_optionResourceModel->insertMultipleOptions($optionsToSave);
            }
        }
        return $this;
    }
}
