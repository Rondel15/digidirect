<?php
/**
 * @package     Plumrocket_Newsletterpopup
 * @copyright   Copyright (c) 2019 Plumrocket Inc. (https://plumrocket.com)
 * @license     https://plumrocket.com/license   End-user License Agreement
 */

namespace Plumrocket\Newsletterpopup\Model;

use Magento\Customer\Model\Session;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\HTTP\PhpEnvironment\RemoteAddress;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Model\Context;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Registry;
use Magento\Store\Model\StoreManager;
use Plumrocket\Newsletterpopup\Api\Data\HistoryInterface;
use Plumrocket\Newsletterpopup\Helper\Config;
use Plumrocket\Newsletterpopup\Helper\DateTime;
use Plumrocket\Newsletterpopup\Model\Config\Source\Action;

class History extends AbstractModel implements HistoryInterface
{
    private $_checkEnabled = true;
    private $_checkIpSkip = true;

    private $_remoteAddress;
    private $_session;
    private $_request;
    private $_storeManager;

    /**
     * @var \Plumrocket\Newsletterpopup\Helper\DateTime
     */
    private $dateTime;

    /**
     * @var \Plumrocket\Newsletterpopup\Helper\Config
     */
    private $config;

    /**
     * @param \Magento\Framework\Model\Context                             $context
     * @param \Magento\Framework\Registry                                  $registry
     * @param \Magento\Framework\HTTP\PhpEnvironment\RemoteAddress         $remoteAddress
     * @param \Magento\Customer\Model\Session                              $session
     * @param \Magento\Framework\App\RequestInterface                      $request
     * @param \Magento\Store\Model\StoreManager                            $storeManager
     * @param \Plumrocket\Newsletterpopup\Helper\DateTime                  $dateTime
     * @param \Plumrocket\Newsletterpopup\Helper\Config                    $config
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource|null $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb|null           $resourceCollection
     * @param array                                                        $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        RemoteAddress $remoteAddress,
        Session $session,
        RequestInterface $request,
        StoreManager $storeManager,
        DateTime $dateTime,
        Config $config,
        AbstractResource $resource = null,
        AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->_remoteAddress = $remoteAddress;
        $this->_session = $session;
        $this->_request = $request;
        $this->_storeManager = $storeManager;
        $this->dateTime = $dateTime;
        $this->config = $config;
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
    }

    protected function _construct()
    {
        $this->_init(ResourceModel\History::class);
    }

    public function beforeSave()
    {
        if (! $this->_checkEnabled || $this->config->isHistoryEnabled()) {
            $customerIp = $this->_remoteAddress->getRemoteAddress();

            if ($this->_checkIpSkip) {
                if ($this->getAction() != Action::SUBSCRIBE && $this->isDisableHistoryForIp($customerIp)) {
                    $this->_dataSaveAllowed = false;
                    return $this;
                }
            }

            $customer = $this->_session->isLoggedIn()? $this->_session->getCustomer() : false;

            $path = str_replace(['http://', 'https://'], '', (string)$this->_request->getParam('referer'));
            $path = substr($path, strpos($path, '/'));

            $data = array_merge([
                'customer_id'       => $customer ? $customer->getId() : 0,
                'customer_group'    => $customer ? $customer->getGroupId() : 0,
                'customer_ip'       => $customerIp,
                'landing_page'      => $path,
                // 'store_id'          => $this->_storeManager->getStore()->getStoreId(),
                'store_id'          => $this->_storeManager->getStore()->getId(),
                'date_created'      => $this->dateTime->format(time(), 'YYYY-MM-dd hh:mm:ss'),
            ], $this->getData());

            $this->setData($data);
        } else {
            $this->_dataSaveAllowed = false;
        }

        return $this;
    }

    public function save()
    {
        if ($this->getAction() == Action::OTHER && $this->getActionText()) {
            if (! $id = $this->_getResource()->insertOnDuplicate($this->_getResource()->getActionMainTable(), ['text' => trim($this->getActionText())], ['text'])) {
                $connection = $this->_getResource()->getConnection('read');
                $id = $connection->fetchOne(
                    'SELECT `id` FROM ' . $this->_getResource()->getActionMainTable() . ' WHERE `text` = ? LIMIT 1',
                    trim($this->getActionText())
                );
            }

            if (!empty($id)) {
                $this->setActionId($id);
                $this->unsActionText();
            }
        }

        $this->setAction(ucfirst($this->getAction()));

        return parent::save();
    }

    public function checkEnabled($flag = null)
    {
        if (null !== $flag) {
            $this->_checkEnabled = (bool)$flag;
        }
        return $this->_checkEnabled;
    }

    public function checkIpSkip($flag = null)
    {
        if (null !== $flag) {
            $this->_checkIpSkip = (bool)$flag;
        }
        return $this->_checkIpSkip;
    }

    /**
     * @param $customerIp
     * @return bool
     */
    protected function isDisableHistoryForIp($customerIp)
    {
        if (! empty($customerIp) && is_string($customerIp)) {
            foreach ($this->config->getSkipIps() as $ip) {
                if (substr($ip, -1) === '*') {
                    $ip = substr($ip, 0, -1);
                    if (strpos($customerIp, $ip) === 0) {
                        return true;
                    }
                } elseif ($ip == $customerIp) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * @inheritDoc
     */
    public function setAction(string $action): HistoryInterface
    {
        return $this->setData(self::ACTION, $action);
    }

    /**
     * @inheritDoc
     */
    public function setPopupId(int $popupId): HistoryInterface
    {
        return $this->setData(self::POPUP_ID, $popupId);
    }
}
