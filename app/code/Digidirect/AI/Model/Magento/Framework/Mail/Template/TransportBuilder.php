<?php

namespace Digidirect\AI\Model\Magento\Framework\Mail\Template;

/**
 * Class TransportBuilder
 * @package Digidirect\AI\Model\Magento\Framework\Mail\Template
 * @deprecated
 */
class TransportBuilder extends \Magento\Framework\Mail\Template\TransportBuilder
{
    /**
     * Attach File
     *
     * @param $file string
     * @param $name string
     * @return $this
     */
    public function attachFile($file, $name)
    {
        if (!empty($file) && file_exists($file)) {
            $this->message
                ->createAttachment(
                    file_get_contents($file),
                    \Zend_Mime::TYPE_OCTETSTREAM,
                    \Zend_Mime::DISPOSITION_ATTACHMENT,
                    \Zend_Mime::ENCODING_BASE64,
                    basename($name)
                );
        }
        return $this;
    }

    /**
     * Reset object state
     *
     * @return $this
     */
    public function resetObjectState()
    {
        return parent::reset();
    }
}
