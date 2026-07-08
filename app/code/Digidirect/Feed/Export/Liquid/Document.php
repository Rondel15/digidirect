<?php

namespace Digidirect\Feed\Export\Liquid;

/**
 * @codingStandardsIgnoreFile
 * @SuppressWarnings(PHPMD)
 */
class Document extends Block
{

    /**
     * Constructor
     *
     * @param array $tokens
     */
    public function __construct(array $tokens)
    {
        $this->parse($tokens);

        return $this;
    }

    /**
     * Check for cached includes
     *
     * @return string
     */
    public function checkIncludes()
    {
        $return = false;
        foreach ($this->nodeList as $token) {
            if (is_object($token)) {
                if (get_class($token) == 'LiquidTagInclude' || get_class($token) == 'LiquidTagExtends') {
                    if ($token->checkIncludes() == true) {
                        $return = true;
                    }
                }
            }
        }
        return $return;
    }

    /**
     * There isn't a real delimiter
     *
     * @return string
     */
    public function blockDelimiter()
    {
        return '';
    }

    /**
     * Document blocks don't need to be terminated since they are not actually opened
     *
     * @return void
     */
    public function assertMissingDelimitation()
    {
    }
}
