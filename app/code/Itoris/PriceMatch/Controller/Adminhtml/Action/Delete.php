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

class Delete extends \Magento\Backend\App\Action
{
    private $priceMatchFactory;

    public function __construct
    (
        Action\Context $context,
        \Itoris\PriceMatch\Model\PriceMatchFactory $priceMatchFactory
    )
    {
        parent::__construct($context);
        $this->priceMatchFactory = $priceMatchFactory;
    }

    public function execute()
    {
        if( $this->_request->getParam('id') ){
            $this->priceMatchFactory->create()->load((int)$this->_request->getParam('id'))->delete();
        }

        $this->messageManager->addSuccess(__('The price match request has been removed'));

        return $this->resultRedirectFactory->create()->setPath('itorispm/index/index');
    }
}