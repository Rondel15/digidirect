<?php
/**
 * @package     Plumrocket_Newsletterpopup
 * @copyright   Copyright (c) 2017 Plumrocket Inc. (https://plumrocket.com)
 * @license     https://plumrocket.com/license   End-user License Agreement
 */

namespace Plumrocket\Newsletterpopup\Model\ResourceModel\Customer;

use Magento\Customer\Model\ResourceModel\Customer as CustomerResource;
use Magento\Eav\Model\Entity\Context;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Model\ResourceModel\Db\VersionControl\RelationComposite;
use Magento\Framework\Model\ResourceModel\Db\VersionControl\Snapshot;
use Magento\Framework\Stdlib\DateTime;
use Magento\Framework\Validator\Factory;
use Magento\Store\Model\StoreManagerInterface;
use Plumrocket\Base\Model\Utils\Config;
use Plumrocket\Newsletterpopup\Helper\Data;

class Customer extends CustomerResource
{
    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    protected $_request;

    /**
     * @var \Plumrocket\Base\Model\Utils\Config
     */
    protected $configUtils;

    /**
     * @param \Magento\Eav\Model\Entity\Context                                          $context
     * @param \Magento\Framework\Model\ResourceModel\Db\VersionControl\Snapshot          $entitySnapshot
     * @param \Magento\Framework\Model\ResourceModel\Db\VersionControl\RelationComposite $entityRelationComposite
     * @param \Magento\Framework\App\Config\ScopeConfigInterface                         $scopeConfig
     * @param \Magento\Framework\Validator\Factory                                       $validatorFactory
     * @param \Magento\Framework\Stdlib\DateTime                                         $dateTime
     * @param \Magento\Store\Model\StoreManagerInterface                                 $storeManager
     * @param \Magento\Framework\App\RequestInterface                                    $request
     * @param \Plumrocket\Base\Model\Utils\Config                                        $configUtils
     * @param array                                                                      $data
     */
    public function __construct(
        Context $context,
        Snapshot $entitySnapshot,
        RelationComposite $entityRelationComposite,
        ScopeConfigInterface $scopeConfig,
        Factory $validatorFactory,
        DateTime $dateTime,
        StoreManagerInterface $storeManager,
        RequestInterface $request,
        Config $configUtils,
        $data = []
    ) {
        $this->_request = $request;
        $this->configUtils = $configUtils;
        parent::__construct(
            $context,
            $entitySnapshot,
            $entityRelationComposite,
            $scopeConfig,
            $validatorFactory,
            $dateTime,
            $storeManager,
            $data
        );
    }

    /**
     * Validate customer entity
     *
     * @param \Magento\Customer\Model\Customer $customer
     * @return void
     * @throws \Magento\Framework\Validator\Exception
     */
    protected function _validate($customer)
    {
        if ($this->configUtils->getConfig('prnewsletterpopup/general/enable')
            && $this->_request->getModuleName() === Data::SECTION_ID
            && $this->_request->getActionName() === 'subscribe'
        ) {
            return; // skip magento validation for our action
        }

        parent::_validate($customer);
    }
}
