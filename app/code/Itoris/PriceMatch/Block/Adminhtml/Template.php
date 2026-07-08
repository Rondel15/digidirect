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

namespace Itoris\PriceMatch\Block\Adminhtml;

class Template extends \Magento\Backend\Block\Template
{
    protected $_template = 'Itoris_PriceMatch::send-button.phtml';

    public function getUrlRedirect()
    {
        return $this->_urlBuilder->getUrl('itorispm/action/applyform', ['id' => $this->_request->getParam('id')]);
    }

    public function getUrlActionForm()
    {
        return $this->_urlBuilder->getUrl('itorispm/action/applyform');
    }

    public function getItemId()
    {
        return $this->_request->getParam('id');
    }

}