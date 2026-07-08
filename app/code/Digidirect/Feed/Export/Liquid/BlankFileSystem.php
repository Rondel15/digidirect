<?php

namespace Digidirect\Feed\Export\Liquid;

/**
 * @codingStandardsIgnoreFile
 * @SuppressWarnings(PHPMD)
 */

class BlankFileSystem
{
    /**
     * Retrieve a template file
     *
     * @param string $templatePath
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @throws \Exception
     */
    public function readTemplateFile()
    {
        throw new \Exception("This liquid context does not allow includes.");
    }
}
