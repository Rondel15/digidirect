<?php

namespace Mbs\BestSeller\Setup;

use Magento\Catalog\Setup\CategorySetup;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Setup\InstallDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;

class InstallData implements InstallDataInterface
{
    /**
     * @var CategorySetup
     */
    private $categorySetup;

    public function __construct(
        CategorySetup $categorySetup
    ) {
        $this->categorySetup = $categorySetup;
    }

    /**
     * @inheritDoc
     */
    public function install(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();

        try {
            $this->categorySetup->addAttribute(
                \Magento\Catalog\Model\Product::ENTITY,
                \Mbs\BestSeller\Model\ProductSalesHandler::SALE_ATTRIBUTE,
                [
                    'type' => 'int',
                    'label' => 'Best Sellers',
                    'required' => 0,
                    'group' => 'General',
                    'used_for_sort_by' => 1,
                    'used_in_product_listing' => 1
                ]
            );
        } catch (LocalizedException $e) {

        } catch (\Zend_Validate_Exception $e) {

        }

        $setup->endSetup();
    }
}
