<?php
/**
 * @package     Plumrocket_Newsletterpopup
 * @copyright   Copyright (c) 2017 Plumrocket Inc. (https://plumrocket.com)
 * @license     https://plumrocket.com/license   End-user License Agreement
 */

namespace Plumrocket\Newsletterpopup\Model\ResourceModel\Customer;

use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Model\ResourceModel\Address as AddressResource;
use Magento\Eav\Model\Entity\Context;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Model\ResourceModel\Db\VersionControl\RelationComposite;
use Magento\Framework\Model\ResourceModel\Db\VersionControl\Snapshot;
use Magento\Framework\Validator\Factory;
use Plumrocket\Base\Model\Utils\Config;
use Plumrocket\Newsletterpopup\Helper\Data;

class Address extends AddressResource
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
     * @param \Magento\Framework\Validator\Factory                                       $validatorFactory
     * @param \Magento\Customer\Api\CustomerRepositoryInterface                          $customerRepository
     * @param \Magento\Framework\App\RequestInterface                                    $request
     * @param \Plumrocket\Base\Model\Utils\Config                                        $configUtils
     * @param array                                                                      $data
     */
    public function __construct(
        Context $context,
        Snapshot $entitySnapshot,
        RelationComposite $entityRelationComposite,
        Factory $validatorFactory,
        CustomerRepositoryInterface $customerRepository,
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
            $validatorFactory,
            $customerRepository,
            $data
        );
    }

    /**
     * Validate customer address entity
     *
     * @param \Magento\Framework\DataObject $address
     * @return void
     * @throws \Magento\Framework\Validator\Exception When validation failed
     */
    protected function _validate($address)
    {
        if ($this->configUtils->getConfig('prnewsletterpopup/general/enable')
            && $this->_request->getModuleName() === Data::SECTION_ID
            && $this->_request->getActionName() === 'subscribe'
        ) {
            return; // skip magento validation for our action
        }

        parent::_validate($address);
    }
}
