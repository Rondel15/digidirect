<?php

namespace Digidirect\Digi\Plugin\Digidirect\LayeredNavigation\Helper;

use Digidirect\Digi\Model\SeoBrandDescription;

/**
 * Class Meta
 * @package Digidirect\Digi\Plugin\Digidirect\LayeredNavigation\Helper
 */
class Meta
{
    /**
     * @var SeoBrandDescription
     */
    private $seoBrandDescription;

    /**
     * Meta constructor.
     * @param SeoBrandDescription $seoBrandDescription
     */
    public function __construct(
        SeoBrandDescription $seoBrandDescription
    ) {
        $this->seoBrandDescription = $seoBrandDescription;
    }

    /**
     * @param $subject
     * @param $result
     * @return array
     */
    public function afterSetPageTags($subject, $result)
    {
        $this->seoBrandDescription->setDefaultMetaInformation();
        return [$result];
    }
}
