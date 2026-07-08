<?php
namespace Digidirect\Digi\Helper;


use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Class AbstractEntities
 * @package Digidirect\Digi\Helper
 */
class AbstractAttribute extends \Magento\Framework\App\Helper\AbstractHelper
{
    const BRAND_ATTRIBUTE_CODE = 'brand';

    /**
     * @var \Magento\Framework\DB\Adapter\AdapterInterface
     */
    protected $connection;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    private $brandCache = [];

    /**
     * AbstractAttribute constructor.
     * @param \Magento\Framework\App\Helper\Context $context
     * @param ResourceConnection $resource
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        ResourceConnection $resource,
        StoreManagerInterface $storeManager
    ) {
        parent::__construct($context);
        $this->connection = $resource->getConnection();
        $this->storeManager = $storeManager;
    }

    /**
     * @return int
     */
    protected function getStoreId()
    {
        try {
            $storeId = $this->storeManager->getStore()->getId();
        } catch (NoSuchEntityException $e) {
            $storeId = 0;
        }

        return $storeId;
    }

    /**
     * @param string $brandUrlKey
     * @return int
     */
    public function getBrandIdByUrlKey(string $brandUrlKey)
    {
        if (!isset($this->brandCache['row'][$brandUrlKey])) {
            $select = $this->connection->select()
                ->from(['aa' => 'digidirect_aa_options'], 'row_id')
                ->joinLeft(['eao' => 'eav_attribute_option'], 'aa.option_id = eao.option_id', [])
                ->joinLeft(['eav' => 'eav_attribute'], 'eao.attribute_id = eav.attribute_id', [])
                ->where('eav.attribute_code = :brand AND aa.url_key = :url_key')
                ->limit(1);

            $this->brandCache['row'][$brandUrlKey] = $this->connection->fetchOne(
                $select,
                [
                    'brand' => self::BRAND_ATTRIBUTE_CODE,
                    'url_key' => $brandUrlKey
                ]
            );
        }
        return (int) $this->brandCache['row'][$brandUrlKey];
    }

    /**
     * @param int $rowId
     * @return string
     */
    public function getBrandLabel(int $rowId)
    {
        if (!isset($this->brandCache['label'][$rowId])) {
            $select = $this->connection->select()
                ->from(['eao' => 'eav_attribute_option_value'], 'value')
                ->joinLeft(['aa' => 'digidirect_aa_options'], 'aa.option_id = eao.option_id', [])
                ->where('aa.row_id = :row_id AND eao.store_id IN (:store_id, 0)')
                ->order('eao.store_id DESC')
                ->limit(1);



            $this->brandCache['label'][$rowId] = $this->connection->fetchOne(
                $select,
                [
                    'row_id' => $rowId,
                    'store_id' => $this->getStoreId()
                ]
            );
        }


        return $this->brandCache['label'][$rowId];
    }

    /**
     * Hotfix duplicate because getBrandIdByUrlKey is using in other places with row_id
     *
     * @param string $brandUrlKey
     * @return int
     */
    public function getBrandOptionIdByUrlKey(string $brandUrlKey)
    {
        if (!isset($this->brandCache['option'][$brandUrlKey])) {
            $select = $this->connection->select()
                ->from(['aa' => 'digidirect_aa_options'], 'option_id')
                ->joinLeft(['eao' => 'eav_attribute_option'], 'aa.option_id = eao.option_id', [])
                ->joinLeft(['eav' => 'eav_attribute'], 'eao.attribute_id = eav.attribute_id', [])
                ->where('eav.attribute_code = :brand AND aa.url_key = :url_key')
                ->limit(1);

            $this->brandCache['option'][$brandUrlKey] = $this->connection->fetchOne(
                $select,
                [
                    'brand' => self::BRAND_ATTRIBUTE_CODE,
                    'url_key' => $brandUrlKey
                ]
            );
        }

        return (int) $this->brandCache['option'][$brandUrlKey];
    }

    /**
     * @param int $optionId
     * @return string
     */
    public function getOptionLabel($optionId)
    {
        $select = $this->connection->select()
            ->from(['eao' => 'eav_attribute_option_value'], 'value')
            ->where('eao.option_id = :option_id AND eao.store_id IN (:store_id, 0)')
            ->order('eao.store_id DESC')
            ->limit(1);

        $label = $this->connection->fetchOne(
            $select,
            [
                'option_id' => $optionId,
                'store_id' => $this->getStoreId()
            ]
        );

        return $label;
    }
}
