<?php
/**
 * ITORIS
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the ITORIS's Magento Extensions License Agreement
 * which is available through the world-wide-web at this URL:
 * http://www.itoris.com/magento-extensions-license.html
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to sales@itoris.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade the extensions to newer
 * versions in the future. If you wish to customize the extension for your
 * needs please refer to the license agreement or contact sales@itoris.com for more information.
 *
 * @category   ITORIS
 * @package    ITORIS_M2_ITORIS_PRICE_MATCH
 * @copyright  Copyright (c) 2018 ITORIS INC. (http://www.itoris.com)
 * @license    http://www.itoris.com/magento-extensions-license.html  Commercial License
 */

namespace Itoris\PriceMatch\Controller\Adminhtml\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;

use Magento\Ui\Component\MassAction\Filter;

class Massremove extends \Magento\Backend\App\Action
{
    protected $collectionPriceMatchFactory;

    public function __construct(
        Context $context,
        \Itoris\PriceMatch\Model\ResourceModel\PriceMatch\CollectionFactory $collectionPriceMatchFactory
    ) {
        parent::__construct($context);
        $this->collectionPriceMatchFactory = $collectionPriceMatchFactory;
    }

    public function execute()
    {
        $selected = $this->_request->getParam('selected');
        $excluded = $this->_request->getParam('excluded');

        /** @var \Itoris\PriceMatch\Model\ResourceModel\PriceMatch\Collection $collection */
        $collection = $this->collectionPriceMatchFactory->create();
  //      $count = $collection->getItorisCount();

        if( $excluded === NULL ){
            if(is_array($selected)){
                $collection->addFieldToFilter('item_id', ['in'=>$selected]);
  //              $countFilter = $collection->getItorisCount();
                foreach ($collection as $item){
                    $item->delete();
                }
            }

        }else{
    //        $countFilter = $collection->getItorisCount();
            foreach ($collection as $item){
                $item->delete();
            }
        }

        $this->messageManager->addSuccess(__('Selected requests have been removed'));

        return $this->resultRedirectFactory->create()->setPath('itorispm/index/index');
    }

}
