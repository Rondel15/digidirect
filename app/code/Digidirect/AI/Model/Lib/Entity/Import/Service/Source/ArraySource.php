<?php
namespace Digidirect\AI\Model\Lib\Entity\Import\Service\Source;

use Magento\ImportExport\Model\Import\AbstractSource;

class ArraySource extends AbstractSource
{
    /**
     * @var array
     */
    protected $data = [];

    /**
     * ArraySource constructor.
     * @param array $colNames
     * @param array $data
     */
    public function __construct(array $colNames, array $data = [])
    {
        parent::__construct($colNames);
        $this->data = $data;
    }

    /**
     * @param array $entity
     * @return $this
     */
    public function addEntity(array $entity)
    {
        $this->data[] = $entity;
        return $this;
    }

    /**
     * @return array
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * @return array
     */
    protected function _getNextRow()
    {
        if (isset($this->data[$this->_key])) {
            $rowData = $this->data[$this->_key];
            if (empty($this->_colNames)) {
                return $rowData;
            }
            $newRow = [];
            foreach ($this->_colNames as $colName) {
                if (isset($rowData[$colName])) {
                    $newRow[$colName] = $rowData[$colName];
                }
            }
            return $newRow;
        }
        return [];
    }

    /**
     * Return the current element
     *
     * @return array
     */
    public function current()
    {
        return $this->_row;
    }
}
