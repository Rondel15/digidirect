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

namespace Itoris\PriceMatch\Ui\Component\Form\Fieldset;
use Magento\Framework\View\Element\UiComponent\ContextInterface;

class Send extends \Magento\Ui\Component\Form\Fieldset
{
    protected $collectionFactory;
    protected $request;

    public function __construct
    (
        ContextInterface $context,
        \Itoris\PriceMatch\Model\ResourceModel\PriceMatch\CollectionFactory $collectionFactory,
        \Magento\Framework\App\RequestInterface $request,
        $components = [],
        array $data = []
    )
    {
        parent::__construct($context, $components, $data);
        $this->collectionFactory = $collectionFactory;
        $this->request = $request;
    }

    public function prepare()
    {
        $item = $this->collectionFactory->create()->sendItemById($this->request->getParam('id'));
        parent::prepare();

        if ($item['status'] != \Itoris\PriceMatch\Model\PriceMatch::STATUS_PENDING) {
            $this->_data['config']['visible'] = false;
        }
    }
}