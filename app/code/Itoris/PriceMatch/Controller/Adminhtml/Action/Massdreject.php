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
use Itoris\PriceMatch\Model\PriceMatch;

class Massdreject extends \Magento\Backend\App\Action
{
    protected $collectionPriceMatchFactory;
    protected $timezone;
    protected $senderCustomer;

    public function __construct(
        Context $context,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $timezone,
        \Itoris\PriceMatch\Model\SenderCustomer $senderCustomer,
        \Itoris\PriceMatch\Model\ResourceModel\PriceMatch\CollectionFactory $collectionPriceMatchFactory
    ) {
        parent::__construct($context);
        $this->collectionPriceMatchFactory = $collectionPriceMatchFactory;
        $this->senderCustomer = $senderCustomer;
        $this->timezone = $timezone;
    }

    public function execute()
    {
        $selected = $this->_request->getParam('selected');
        $excluded = $this->_request->getParam('excluded');
        /** @var \Itoris\PriceMatch\Model\ResourceModel\PriceMatch\Collection $collection */
        $collection = $this->collectionPriceMatchFactory->create();
        $count = $collection->getItorisCount();

        $date = $this->timezone->date()->format('Y-m-d');


        if( $excluded === NULL ){
            if(is_array($selected)){

                $collection->addFieldToFilter('item_id', ['in'=>$selected]);
                $collection->filterPending();
    //            $countFilter = $collection->getItorisCount();
                foreach ($collection as $item){
                    $item->setStatus( 'rejected' );
                    $item->setDateResponse( $date );
                    $item->setMethod(\Itoris\PriceMatch\Model\PriceMatch::METHOD_REJECT);
     //               $priceMatchExtended = $this->getPriceMatchExtended($id);
                    $priceMatchExtended = $item->getData();
                    $priceMatchExtended['admin_response'] = '';
                    $this->sendEmail($priceMatchExtended, PriceMatch::METHOD_REJECT);
                    $item->save();
                }
            }

        }else{
            $coll = $collection->filterPending();
   //         $countFilter = $coll->getItorisCount();
            foreach ($coll as $item){
                $item->setStatus( 'rejected' );
                $item->setDateResponse( $date );
                $item->setMethod(\Itoris\PriceMatch\Model\PriceMatch::METHOD_REJECT);
                $priceMatchExtended = $item->getData();
                $priceMatchExtended['admin_response'] = '';
                $this->sendEmail($priceMatchExtended, PriceMatch::METHOD_REJECT);
                $item->save();
            }
        }

        $this->messageManager->addSuccess(__('Selected requests have been rejected'));
        return $this->resultRedirectFactory->create()->setPath('itorispm/index/index');
    }

    private function sendEmail($item, $method)
    {
        $this->senderCustomer->send($item, $method);
    }

}