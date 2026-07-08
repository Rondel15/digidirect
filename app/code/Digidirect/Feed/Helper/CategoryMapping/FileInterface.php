<?php

namespace Digidirect\Feed\Helper\CategoryMapping;

interface FileInterface extends ReaderInterface
{
    /**
     * @param string $file
     * @return $this
     */
    public function setFile($file);

    /**
     * @return string
     */
    public function getFile();
}
