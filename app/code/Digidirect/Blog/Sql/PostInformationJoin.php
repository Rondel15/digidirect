<?php

namespace Digidirect\Blog\Sql;

use Digidirect\Blog\Api\Data\PostContentInterface;
use Digidirect\Blog\Api\Data\PostInterface;
use Magento\Framework\DB\Select;
use Magento\Store\Model\Store;

class PostInformationJoin implements InformationJoinInterface, CurrentStoreContentCheckerInterface
{
    const DEFAULT_STORE_COLUMN_PREFIX = 'default_';

    /**
     * @var PostInformationSave
     */
    protected $postSave;

    /**
     * PostInformationJoin constructor.
     *
     * @param PostInformationSave $postInformationSave
     */
    public function __construct(
        PostInformationSave $postInformationSave
    ) {
        $this->postSave = $postInformationSave;
    }

    /**
     * @param Select $select
     * @param int $storeId
     * @param null|string $mainTableAlias
     * @param null $id
     * @param array $fields
     * @return Select
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function join(
        Select $select,
        int $storeId,
        $mainTableAlias = PostInterface::DIGIDIRECT_BLOG_POST_TABLE,
        $id = null,
        $fields = []
    ) {
        return $this->doJoin(
            $select,
            $storeId,
            $mainTableAlias,
            PostContentInterface::DIGIDIRECT_BLOG_POST_INFORMATION_TABLE,
            '',
            $id
        );
    }

    /**
     * @param Select $select
     * @param int $storeId
     * @param string $mainTableAlias
     * @param string $joinTableAlias
     * @param string $columnPrefix
     * @param null $id
     * @return Select
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function doJoin(
        Select $select,
        $storeId,
        $mainTableAlias = PostInterface::DIGIDIRECT_BLOG_POST_TABLE,
        $joinTableAlias = PostContentInterface::DIGIDIRECT_BLOG_POST_INFORMATION_TABLE,
        $columnPrefix = '',
        $id = null
    ) {
        if ($id && !$this->hasStoreViewContent($id, $storeId)) {
            $storeId = 0;
        }
        $selectFields = $this->getColumns();

        if ($columnPrefix) {
            foreach ($selectFields as $key => $field) {
                $selectFields[$columnPrefix . $field] = $field;
                unset($selectFields[$key]);
            }
        }

        $select->joinLeft(
            [$joinTableAlias => PostContentInterface::DIGIDIRECT_BLOG_POST_INFORMATION_TABLE],
            $mainTableAlias . '.entity_id = ' . $joinTableAlias . '.'
            . PostContentInterface::DIGIDIRECT_BLOG_POST_INFORMATION_TABLE_ID .
            ' AND ' . $joinTableAlias . '.' . PostContentInterface::STORE_ID . ' = ' . $storeId,
            $selectFields
        );
        return $select;
    }

    /**
     * @param Select $select
     * @param int $storeId
     * @param string $mainTableAlias
     * @param null $id
     * @throws \Magento\Framework\Exception\LocalizedException
     * @return null
     */
    public function joinDefault(
        Select $select,
        $storeId = Store::DEFAULT_STORE_ID,
        $mainTableAlias = PostInterface::DIGIDIRECT_BLOG_POST_TABLE,
        $id = null
    ) {
        $this->doJoin(
            $select,
            $storeId,
            $mainTableAlias,
            static::DEFAULT_STORE_COLUMN_PREFIX . PostContentInterface::DIGIDIRECT_BLOG_POST_INFORMATION_TABLE,
            static::DEFAULT_STORE_COLUMN_PREFIX,
            $id
        );
    }

    /**
     * @param int $entityId
     * @param int $storeId
     * @return bool
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function hasStoreViewContent(int $entityId, int $storeId): bool
    {
        return $this->postSave->hasStoreViewContent(...func_get_args());
    }

    /**
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function getColumns()
    {
        return $this->postSave->getMainTableColumns();
    }

    /**
     * @return string
     */
    public function getDefaultContentPrefix()
    {
        return $this->postSave->getDefaultContentPrefix();
    }
}
