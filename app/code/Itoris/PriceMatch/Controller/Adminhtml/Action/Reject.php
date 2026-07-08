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
use Magento\Backend\App\Action;
use Itoris\PriceMatch\Model\PriceMatch;

class Reject extends \Magento\Backend\App\Action
{
    private $priceMatchFactory;
    private $timezone;
    private $senderCustomer;

    public function __construct
    (
        Action\Context $context,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $timezone,
        \Itoris\PriceMatch\Model\SenderCustomer $senderCustomer,
        \Itoris\PriceMatch\Model\PriceMatchFactory $priceMatchFactory
    )
    {
        parent::__construct($context);
        $this->priceMatchFactory = $priceMatchFactory;
        $this->senderCustomer = $senderCustomer;
        $this->timezone = $timezone;
    }

    public function execute()
    {
        if( $this->_request->getParam('id') ){
            $date = $this->timezone->date()->format('Y-m-d');
            $item = $this->priceMatchFactory->create()->load((int)$this->_request->getParam('id'))
                ->setStatus(\Itoris\PriceMatch\Model\PriceMatch::STATUS_REJECTED)
                ->setMethod(\Itoris\PriceMatch\Model\PriceMatch::METHOD_REJECT)
                ->setDateResponse($date);
            $priceMatchExtended = $this->getPriceMatchExtended( $this->_request->getParam('id') );
            $priceMatchExtended['admin_response'] = '';
            $this->sendEmail($priceMatchExtended, PriceMatch::METHOD_REJECT);
            $item->save();
        }

        $this->messageManager->addSuccess(__('The price match request has been rejected.'));

        return $this->resultRedirectFactory->create()->setPath('itorispm/index/index');
    }

    private function getPriceMatchExtended($id)
    {
        return $this->priceMatchFactory->create()->getCollection()->sendItemById($id);
    }

    private function sendEmail($item, $method)
    {
        $this->senderCustomer->send($item, $method);
    }
}