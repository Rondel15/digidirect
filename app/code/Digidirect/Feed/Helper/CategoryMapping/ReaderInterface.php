<?php

namespace Digidirect\Feed\Helper\CategoryMapping;

interface ReaderInterface
{
    /**
     * @param int $limit
     * @return $this
     */
    public function setLimit($limit);

    /**
     * @return int
     */
    public function getLimit();

    /**
     * @param string $search
     * @return array
     */
    public function getRows($search);
}
