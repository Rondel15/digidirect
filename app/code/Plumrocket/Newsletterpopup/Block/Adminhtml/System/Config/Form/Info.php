<?php
/**
 * @package     Plumrocket_Newsletterpopup
 * @copyright   Copyright (c) 2017 Plumrocket Inc. (https://plumrocket.com)
 * @license     https://plumrocket.com/license   End-user License Agreement
 */

namespace Plumrocket\Newsletterpopup\Block\Adminhtml\System\Config\Form;

use Magento\Config\Block\System\Config\Form\Field;
use Magento\Framework\Data\Form\Element\AbstractElement;
use Plumrocket\Newsletterpopup\Model\Mailchimp\Error;

class Info extends Field
{
    /**
     * @var \Plumrocket\Newsletterpopup\Helper\Data
     */
    protected $_dataHelper;

    /**
     * @var \Plumrocket\Newsletterpopup\Helper\Adminhtml
     */
    protected $_adminhtmlHelper;

    /**
     * @var \Magento\Framework\Encryption\Encryptor
     */
    protected $_encryptor;

    /**
     * Info constructor.
     *
     * @param \Magento\Backend\Block\Template\Context      $context
     * @param \Plumrocket\Newsletterpopup\Helper\Data      $dataHelper
     * @param \Plumrocket\Newsletterpopup\Helper\Adminhtml $adminhtmlHelper
     * @param \Magento\Framework\Encryption\Encryptor      $encryptor
     * @param array                                        $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Plumrocket\Newsletterpopup\Helper\Data $dataHelper,
        \Plumrocket\Newsletterpopup\Helper\Adminhtml $adminhtmlHelper,
        \Magento\Framework\Encryption\Encryptor $encryptor,
        array $data = []
    ) {
        $this->_dataHelper = $dataHelper;
        $this->_adminhtmlHelper = $adminhtmlHelper;
        $this->_encryptor = $encryptor;
        parent::__construct($context, $data);
    }

    /**
     * @param AbstractElement $element
     * @return string
     */
    protected function _getElementHtml(AbstractElement $element)
    {
        $this->setElement($element);
        $key = trim(
            $this->_encryptor->decrypt(
                $this->_dataHelper->getConfig(
                    'prnewsletterpopup/integration/mailchimp/key'
                )
            )
        );

        $isMailchimpEnabled = $this->_dataHelper->getConfig(
            'prnewsletterpopup/integration/mailchimp/enable'
        );

        if (! $isMailchimpEnabled) {
            $message = 'Mailchimp Synchronization is disabled.';
        } elseif (!$key) {
            $message = 'Mailchimp API Key is not provided.';
        } else {
            $model = $this->_adminhtmlHelper->getMcapi();
            if ($model) {
                try {
                    $message = $model->ping();
                } catch (Error $e) {
                    return $this->buildErrorMessage($e->getMessage());
                }

                if ($message == "Everything's Chimpy!" || $message == '') {
                    $profile = $model->getAccountDetails();

                    if (isset($profile['username']) && $profile['username']) {
                        return sprintf(
                            '<ul class="checkboxes" style="border: 1px solid #ccc; padding: 5px; background-color: #fdfdfd; margin-top: 2px;">
                            <li>Username: %s</li>
                            <li>Plan type: %s</li>
                            <li>Mailchimp Pro: %s</li>
                            </ul>',
                            $profile['username'],
                            $profile['pricing_plan_type'],
                            $profile['pro_enabled']? 'Yes' : 'No'
                        );
                    } else {
                        $message = 'Mailchimp API Key is not valid.';
                    }
                } else {
                    $message = 'Mailchimp server returned error: ' . $message;
                }
            } else {
                $message = 'Connection failed.';
            }
        }
        return $this->buildErrorMessage($message);
    }

    protected function buildErrorMessage($message) {
        return '<div class="checkboxes" style="border: 1px solid #ccc; padding: 5px; background-color: #fdfdfd; margin-top: 2px;">' . $message . '</div>';
    }
}
