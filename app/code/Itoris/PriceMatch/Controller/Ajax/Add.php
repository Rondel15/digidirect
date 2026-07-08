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

namespace Itoris\PriceMatch\Controller\Ajax;
use Magento\Framework\App\Action\Context;

class Add extends \Magento\Framework\App\Action\Action
{
    protected $resultJsonFactory;
    protected $customerSession;
    protected $storeManager;
    protected $productRepository;
    private $timezone;
    protected $priceMatchFactory;
    protected $senderAdmin;
    protected $helperData;

    public function __construct
    (
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Framework\Controller\Result\JsonFactory    $resultJsonFactory,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        \Itoris\PriceMatch\Model\SenderAdmin $senderAdmin,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $timezone,
        \Itoris\PriceMatch\Helper\Data $helperData,
        \Itoris\PriceMatch\Model\PriceMatchFactory $priceMatchFactory,
        Context $context
    )
    {
        $this->customerSession = $customerSession;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->storeManager = $storeManager;
        $this->productRepository = $productRepository;
        $this->timezone = $timezone;
        $this->priceMatchFactory = $priceMatchFactory;
        $this->senderAdmin = $senderAdmin;
        $this->helperData = $helperData;
        parent::__construct($context);
    }

    public function execute()
    {
        $params = $this->_request->getParams();

        $byRequest = isset($params['super_attribute']) ? $params['super_attribute'] : null ;
        /** @var \Itoris\PriceMatch\Model\PriceMatch $priceMatch */
        $priceMatch = $this->priceMatchFactory->create();

        if(isset($params['url']) && $params['url']){
            $priceMatch->setMatchUrl($params['url']);
        }
        if(isset($params['comment']) && $params['comment']){
            $priceMatch->setComment($params['comment']);
        }

        $priceMatch->setProductId($params['id']);
        $priceMatch->setStoreId($this->storeManager->getStore()->getId());
        $priceMatch->setStatus(\Itoris\PriceMatch\Model\PriceMatch::STATUS_PENDING);
        if($byRequest){
            $priceMatch->setByRequest($byRequest);
        }

        $date = $this->timezone->date()->format('Y-m-d');
        $priceMatch->setDateCreated( $date );

        if($this->customerSession->isLoggedIn()){
            $priceMatch->setCustomerId($this->customerSession->getCustomerId());
        }else{
            $priceMatch->setName($params['name']);
            $priceMatch->setEmail($params['email']);
        }
        $priceMatch->setMatchPrice($params['price']);
        $priceMatch->save();

        if( $this->helperData->checkSendAdmin($this->storeManager->getStore()->getId()) ){
            $itemId = $priceMatch->getId();
            $item = $priceMatch->getCollection()->sendItemById($itemId);
            $this->sendEmail($item, null);
        }

        $resultJson = $this->resultJsonFactory->create();
        $response = [
            'status' => 'OK',
            'msg' => __('Thank you for submitting your Price Match request! We will respond to you as soon as possible'),
        ];
        return $resultJson->setData($response);
    }

    private function sendEmail($item, $method)
    {
        $this->senderAdmin->send($item, $method);
    }
}
