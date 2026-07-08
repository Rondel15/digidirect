<?php

namespace Digidirect\Feed\Model;

use Magento\Framework\Model\Context;
use Magento\Framework\Registry;
use Magento\Framework\Serialize\Serializer\Serialize;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\Stdlib\CookieManagerInterface;

/**
 * @method $this setSession(string $session)
 * @method $this setProductId(int $id)
 * @method $this setFeedId(int $id)
 * @method $this setIsOrder(bool $isOrder)
 * @method $this setOrderId(int $id)
 * @method $this setIsClick(bool $isClick)
 * @method $this setStoreId(int $id)
 * @method $this setSubtotal(float $subtotal)
 * @method $this setCreatedAt(string $createdAt)
 */
class Report extends AbstractTemplate
{
    /**
     * Prefix of model events names
     *
     * @var string
     */
    protected $_eventPrefix = 'digidirect_feed_report';

    /**
     * Parameter name in event
     *
     * In observe method you can use $observer->getEvent()->getObject() in this case
     *
     * @var string
     */
    protected $_eventObject = 'report';

    /**
     * @var ReportFactory
     */
    protected $factory;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var CookieManagerInterface
     */
    protected $cookieManager;

    /**
     * Report constructor.
     * @param Context $context
     * @param Registry $registry
     * @param Serialize $serializer
     * @param ReportFactory $factory
     * @param StoreManagerInterface $storeManager
     * @param CookieManagerInterface $cookieManager
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource|null $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb|null $resourceCollection
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        Serialize $serializer,
        ReportFactory $factory,
        StoreManagerInterface $storeManager,
        CookieManagerInterface $cookieManager,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->factory = $factory;
        $this->storeManager = $storeManager;
        $this->cookieManager = $cookieManager;

        parent::__construct($context, $registry, $serializer, $resource, $resourceCollection, $data);
    }

    /**
     * {@inheritdoc}
     */
    protected function _construct()
    {
        $this->_init('Digidirect\Feed\Model\ResourceModel\Report');
    }

    /**
     * @param string $session
     * @param int $feedId
     * @param int $productId
     *
     * @return Report
     */
    public function addClick($session, $feedId, $productId)
    {
        $report = $this->factory->create()
            ->setSession($session)
            ->setFeedId($feedId)
            ->setProductId($productId)
            ->setStoreId($this->storeManager->getStore()->getId())
            ->setIsClick(1)
            ->setCreatedAt((new \DateTime())->format(\Magento\Framework\Stdlib\DateTime::DATETIME_PHP_FORMAT));

        $report->getResource()->save($report);
        return $this;
    }

    /**
     * @param \Magento\Sales\Model\Order $order
     * @return $this
     */
    public function addOrder($order)
    {
        if ($this->cookieManager->getCookie('feed_session')
            && $this->cookieManager->getCookie('feed_id')
        ) {
            $session = $this->cookieManager->getCookie('feed_session');
            $feedId = (int)$this->cookieManager->getCookie('feed_id');

            $report = $this->factory->create()
                ->setSession($session)
                ->setFeedId($feedId)
                ->setStoreId($this->storeManager->getStore()->getId())
                ->setIsOrder(1)
                ->setOrderId($order->getId())
                ->setSubtotal($order->getBaseSubtotal())
                ->setIsClick(0)
                ->setCreatedAt($order->getCreatedAt());
            $report->getResource()->save($report);
        }

        return $this;
    }
}
