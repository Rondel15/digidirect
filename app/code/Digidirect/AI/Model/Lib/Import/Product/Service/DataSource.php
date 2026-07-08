<?php
namespace Digidirect\AI\Model\Lib\Import\Product\Service;

class DataSource implements \IteratorAggregate
{
    /**
     * Bunch
     *
     * @var array
     */
    protected $_bunch = [];

    /**
     * Iterator
     *
     * @var \ArrayIterator
     */
    protected $_iterator;

    /**
     * Get iterator instance
     *
     * @return \Iterator
     */
    public function getIterator(): \Traversable
    {
        $this->_iterator = new \ArrayIterator($this->_bunch);
        return $this->_iterator;
    }

    /**
     * Set bunch
     *
     * @param array $bunch
     * @return void
     */
    public function setBunch($bunch = [])
    {
        $this->_bunch = $bunch;
    }

    /**
     * Get next bunch
     *
     * @return mixed
     */
    public function getNextBunch()
    {
        if (!$this->_iterator) {
            $this->_iterator = $this->getIterator();
            $this->_iterator->rewind();
        }

        $dataRow = null;
        if ($this->_iterator->valid()) {
            $dataRow = $this->_iterator->current();
            $this->_iterator->next();
        }
        if (!$dataRow) {
            $this->_iterator = null;
        }
        return $dataRow;
    }

    /**
     * Clear bunch
     *
     * @return void
     */
    public function clear()
    {
        $this->_bunch = [];
    }
}
