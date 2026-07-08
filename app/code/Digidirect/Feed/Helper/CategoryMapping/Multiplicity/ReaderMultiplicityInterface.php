<?php

namespace Digidirect\Feed\Helper\CategoryMapping\Multiplicity;

use Digidirect\Feed\Helper\CategoryMapping\ReaderInterface;

interface ReaderMultiplicityInterface
{
    /**
     * @return $this
     */
    public function findAll();

    /**
     * @param ReaderInterface $item
     * @return $this
     */
    public function addItem(ReaderInterface $item);

    /**
     * @return array
     */
    public function getItems();
}
