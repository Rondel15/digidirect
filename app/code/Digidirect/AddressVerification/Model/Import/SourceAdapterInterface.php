<?php
namespace Digidirect\AddressVerification\Model\Import;

/**
 * Interface SourceAdapterInterface
 * @package Digidirect\AddressVerification\Model\Import
 */
interface SourceAdapterInterface
{
    /**
     * @return array
     */
    public function parse();
}
