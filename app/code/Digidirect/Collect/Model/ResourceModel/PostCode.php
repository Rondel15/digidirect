<?php
namespace Digidirect\Collect\Model\ResourceModel;

use Digidirect\Collect\Model\PostCode as PostCodeModel;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Magento\Framework\Model\ResourceModel\Db\Context;

class PostCode extends AbstractDb
{
    const COORDINATES_PRECISION = 4;

    /**
     * OrderPdf constructor.
     *
     * @param Context $context
     * @param null $connectionName
     */
    public function __construct(
        Context $context,
        $connectionName = null
    ) {
        parent::__construct($context, $connectionName);
    }

    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(PostCodeModel::POST_CODE_TABLE, PostCodeModel::TABLE_COLUMN_ID);
    }

    /**
     * Save post code data.
     *
     * @param array $data
     * @return int The number of affected rows.
     */
    public function saveData(array $data)
    {
        $connection = $this->getConnection();
        $connection->truncateTable(PostCodeModel::POST_CODE_TABLE);

        return $connection->insertOnDuplicate(
            PostCodeModel::POST_CODE_TABLE,
            $data,
            [PostCodeModel::TABLE_COLUMN_LONGITUDE, PostCodeModel::TABLE_COLUMN_LATITUDE]
        );
    }

    /**
     * Get post code data from base.
     *
     * @param string $postCode
     * @return array Data of affected rows.
     */
    public function getCoordinatesByPostCodeFromDB($postCode)
    {
        $connection = $this->getConnection();
        $select = $connection->select()->distinct()
            ->from(
                $this->getTable(PostCodeModel::POST_CODE_TABLE),
                [PostCodeModel::TABLE_COLUMN_LONGITUDE, PostCodeModel::TABLE_COLUMN_LATITUDE]
            )
            ->where(PostCodeModel::TABLE_COLUMN_POST_CODE . ' = ?', $postCode)
            ->where(PostCodeModel::TABLE_COLUMN_LONGITUDE . ' != ?', null)
            ->where(PostCodeModel::TABLE_COLUMN_LATITUDE . ' != ?', null);

        return $this->getAverageCoordinates($connection->fetchAll($select));
    }

    /**
     * Get average data from coordinates.
     *
     * @param array $coordinates
     * @return array|false Data of coordinates.
     */
    protected function getAverageCoordinates($coordinates)
    {
        $count = count($coordinates);
        if ($count) {
            $longitude = 0;
            $latitude = 0;
            foreach ($coordinates as $coordinate) {
                $longitude += (float)$coordinate[PostCodeModel::TABLE_COLUMN_LONGITUDE];
                $latitude += (float)$coordinate[PostCodeModel::TABLE_COLUMN_LATITUDE];
            }

            $return[PostCodeModel::TABLE_COLUMN_LONGITUDE] = round($longitude / $count, self::COORDINATES_PRECISION);
            $return[PostCodeModel::TABLE_COLUMN_LATITUDE] = round($latitude / $count, self::COORDINATES_PRECISION);

            return $return;
        }

        return false;
    }
}
