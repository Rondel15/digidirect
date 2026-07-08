<?php
namespace Digidirect\Utilities\Model;

/**
 * Interface CustomTemplateVarsInterface
 * @package Digidirect\Utilities\Model
 */
interface CustomTemplateVarsInterface
{
    /**
     * Get template custom variables
     *
     * @param \Magento\Framework\Mail\Template\TransportBuilder $subject
     * @param array|null $templateVars
     * @return array
     */
    public function getVars(\Magento\Framework\Mail\Template\TransportBuilder $subject, array $templateVars = null);
}
