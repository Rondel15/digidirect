<?php
namespace Digidirect\Pronto\Cron;

use Digidirect\Pronto\Helper\Product;

class CronStockStatusUpdate extends \Magento\Framework\App\Action\Action
{
    protected $_pageFactory;

    protected $_productCollectionFactory;

    public function __construct(
            \Magento\Framework\App\Action\Context $context,
            Product $helper,
            \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory,
            \Magento\InventoryApi\Api\SourceItemsSaveInterface $sourceItemsSaveInterface,
            \Magento\InventoryApi\Api\Data\SourceItemInterfaceFactory $sourceItemFactory,
            \Magento\InventoryCatalogAdminUi\Model\GetSourceItemsDataBySku $sourceDataBySku,
            \Magento\Catalog\Model\Product\Attribute\Source\Status $productStatus,
            array $data = [])
    {
            $this->helper = $helper;
            $this->_productCollectionFactory = $productCollectionFactory;
            $this->sourceItemsSaveInterface = $sourceItemsSaveInterface;
            $this->sourceItemFactory = $sourceItemFactory;
            $this->sourceDataBySku = $sourceDataBySku;
            $this->productStatus = $productStatus;
            return parent::__construct($context);
    }

    public function execute(){

//        1. Get enabled Product SKU.
//        2. Iterate collection and check source status by SKU
//        3. Update stock status to 1 if current is 0

        /*Get in stock product collection*/
        $collection = $this->_productCollectionFactory->create()->addFieldToSelect('*')
            ->setFlag('has_stock_status_filter', false)
//            ->addAttributeToFilter('sku', array('like' => '109429%'))
            ->addAttributeToFilter('status', ['in' => $this->productStatus->getVisibleStatusIds()])
            ->joinField('stock_item', 'cataloginventory_stock_item', 'is_in_stock', 'product_id=entity_id');

        foreach ($collection as $key => $product) {

            $sku = $product->getSku();

            $source_status = $this->sourceDataBySku->execute($sku);
            foreach($source_status as $key => $source_data){
                $source_code = $source_data["source_code"];
                $source_qty = $source_data["quantity"];

                settype($source_qty, "integer");

                $source_status = $source_data["status"];

                if($source_status == 0){
                    $sourceItem = $this->sourceItemFactory->create();
                    $sourceItem->setSourceCode($source_code);
                    $sourceItem->setSku($sku);
                    $sourceItem->setStatus(1);

                    $sourceItem->setQuantity($source_qty);
                    $this->sourceItemsSaveInterface->execute([$sourceItem]);
                }
            }

            //echo $product->getName() . " - "  . $product->getSku() . " <br />";
        }

        exit();
    }
}
