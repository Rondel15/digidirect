<?php

namespace Digidirect\Feed\Helper\CategoryMapping\Multiplicity;

use Digidirect\Feed\Helper\CategoryMapping\ReaderInterface;

abstract class ReaderMultiplicity implements ReaderMultiplicityInterface
{
    /**
     * @var array
     */
    protected $items = [];

    /**
     * {@inheritdoc}
     */
    public function addItem(ReaderInterface $item)
    {
        $this->items[] = $item;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getItems()
    {
        return $this->items;
    }

    /**
     * {@inheritdoc}
     */
    public function count()
    {
        return count($this->items);
    }

    /**
     * {@inheritdoc}
     */
    abstract public function findAll();
}
