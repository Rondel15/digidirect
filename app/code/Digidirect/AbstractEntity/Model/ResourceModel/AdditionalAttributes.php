<?php

namespace Digidirect\AbstractEntity\Model\ResourceModel;

use Digidirect\AbstractEntity\Model\AttributeSet\UrlProcessor;
use Digidirect\AbstractEntity\Api\Data\AbstractEntityInterface;
use Digidirect\AbstractEntity\Model\AttributeSet\UrlProcessorFactory;
use \Magento\Framework\Model\ResourceModel\Db\Context;
use \Digidirect\AbstractEntity\Model\RelationInterface;
use \Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use \Magento\Framework\Filter\FilterManager;

/**
 * Class AdditionalAttributes
 * @package Digidirect\AbstractEntity\Model\ResourceModel
 */
class AdditionalAttributes extends AbstractDb implements RelationInterface
{
    const URL_KEY = 'url_key';
    const DESCRIPTION = 'description';
    const VISIBLE_ON_FRONTEND = 'visible_on_frontend';
    const ENTITIES_PER_LISTING_PAGE = 'entities_per_listing_page';
    const DEFAULT_ENTITIES_PER_LISTING_PAGE = 10;

    /**
     * @var UrlProcessorFactory
     */
    protected $urlProcessorFactory;

    /**
     * @var UrlProcessor
     */
    protected $urlProcessor;

    /**
     * @var FilterManager
     */
    protected $_filterManager;

    /**
     * AdditionalAttributes constructor.
     * @param Context $context
     * @param UrlProcessorFactory $urlProcessorFactory
     * @param FilterManager $filterManager,
     * @param null $connectionName
     */
    public function __construct(
        Context $context,
        UrlProcessorFactory $urlProcessorFactory,
        FilterManager $filterManager,
        $connectionName = null
    ) {
        $this->urlProcessorFactory = $urlProcessorFactory;
        $this->_filterManager = $filterManager;
        parent::__construct($context, $connectionName);
    }

    /**
     * Initialize resource model and define main table
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('digidirect_abstractentity_entity_additional_attributes', AbstractEntityInterface::ATTRIBUTE_SET_ID);
    }

    /**
     * @param int $id
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getAdditionalAttributesById($id)
    {
        $select = $this->getConnection()->select()
            ->from(
                $this->getMainTable()
            )
            ->where(AbstractEntityInterface::ATTRIBUTE_SET_ID . ' = ?', $id);

        return $this->getConnection()->fetchRow($select);
    }

    /**
     * @param int $id
     * @param string $attributeName
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getAdditionalAttributeById($id, $attributeName)
    {
        if (!$attributeName) {
            return '';
        }

        $select = $this->getConnection()->select()
            ->from(
                $this->getMainTable(),
                $attributeName
            )
            ->where(AbstractEntityInterface::ATTRIBUTE_SET_ID . ' = ?', $id);

        return $this->getConnection()->fetchOne($select);
    }

    /**
     * @param int $id
     * @param array $data
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function saveUrlKey($id, $data)
    {
        $urlKey = $this->_filterManager->translitUrl(trim($data[self::URL_KEY] ?? ''));
        $name = trim($data['attribute_set_name'] ?? '');
        $description = trim($data[self::DESCRIPTION] ?? '');
        $visibleOnFrontend = $data[self::VISIBLE_ON_FRONTEND] ?? '';
        $entitiesPerPage = $data[self::ENTITIES_PER_LISTING_PAGE] ?? '';
        if (($urlKey === '' || $urlKey === null) && $name) {
            $urlKey = $this->_filterManager->translitUrl($name);
        }

        if ($id) {
            $this->getConnection()->insertOnDuplicate(
                $this->getMainTable(),
                [
                    AbstractEntityInterface::ATTRIBUTE_SET_ID => $id,
                    self::URL_KEY => $urlKey,
                    self::DESCRIPTION => $description,
                    self::VISIBLE_ON_FRONTEND => $visibleOnFrontend,
                    self::ENTITIES_PER_LISTING_PAGE => $entitiesPerPage
                ]
            );
            if ($visibleOnFrontend) {
                $this->_processUrlRewrites($id, $urlKey);
            }
        }

        return $this;
    }

    /**
     * @param int $id
     * @param string $urlKey
     * @return $this
     */
    protected function _processUrlRewrites($id, $urlKey)
    {
        $urlProcessor = $this->getUrlProcessor();
        $urlProcessor->processUrlRewrites($id, $urlKey);
        return $this;
    }

    /**
     * @return UrlProcessor
     */
    protected function getUrlProcessor()
    {
        if (null === $this->urlProcessor) {
            $this->urlProcessor = $this->urlProcessorFactory->create();
        }
        return $this->urlProcessor;
    }

    /**
     * @param int $entityId
     * @param array $data
     * @return $this|mixed
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function processRelation($entityId, $data)
    {
        $this->saveUrlKey($entityId, $data);
        return $this;
    }
}
