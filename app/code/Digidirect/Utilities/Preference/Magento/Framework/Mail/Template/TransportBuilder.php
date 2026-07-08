<?php

namespace Digidirect\Utilities\Preference\Magento\Framework\Mail\Template;

use Digidirect\Utilities\Model\CustomTemplateVarsInterface;
use Magento\Email\Model\ResourceModel\Template\CollectionFactory as EmailCollectionFactory;

/**
 * Class TransportBuilder
 *
 * @package Digidirect\Utilities\Preference\Magento\Framework\Mail\Template
 */
class TransportBuilder extends \Magento\Framework\Mail\Template\TransportBuilder
{
    /**
     * @var \Digidirect\Utilities\Model\Mail\MessageFactory
     */
    protected $messageCreateAttachmentFactory;

    /**
     * @var \Digidirect\Utilities\Model\CustomTemplateVarsInterface[]
     */
    protected $customTemplateVars;

    /**
     * @var EmailCollectionFactory
     */
    protected $emailCollectionFactory;

    /**
     * @var array
     */
    protected $recipients;

    /**
     * @var bool
     */
    protected $isMessageCreateAttachmentShouldBeUsed;

    /**
     * TransportBuilder constructor.
     *
     * @param \Magento\Framework\Mail\Template\FactoryInterface $templateFactory
     * @param \Magento\Framework\Mail\MessageInterface $message
     * @param \Magento\Framework\Mail\Template\SenderResolverInterface $senderResolver
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param \Magento\Framework\Mail\TransportInterfaceFactory $mailTransportFactory
     * @param \Magento\Framework\Mail\MessageInterfaceFactory $messageFactory
     * @param \Digidirect\Utilities\Model\Mail\MessageFactory $messageCreateAttachmentFactory
     * @param EmailCollectionFactory $emailCollectionFactory
     * @param array $customTemplateVars
     */
    public function __construct(
        \Magento\Framework\Mail\Template\FactoryInterface $templateFactory,
        \Magento\Framework\Mail\MessageInterface $message,
        \Magento\Framework\Mail\Template\SenderResolverInterface $senderResolver,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Framework\Mail\TransportInterfaceFactory $mailTransportFactory,
        array $customTemplateVars = [],
        EmailCollectionFactory $emailCollectionFactory = null,
        \Magento\Framework\Mail\MessageInterfaceFactory $messageFactory = null,
        \Digidirect\Utilities\Model\Mail\MessageFactory $messageCreateAttachmentFactory = null
    ) {
        parent::__construct(
            $templateFactory,
            $message,
            $senderResolver,
            $objectManager,
            $mailTransportFactory,
            $messageFactory
        );
        $this->messageCreateAttachmentFactory = $messageCreateAttachmentFactory ?: $this->objectManager->create(
            \Digidirect\Utilities\Model\Mail\MessageFactory::class
        );
        $this->emailCollectionFactory = $emailCollectionFactory ?: $this->objectManager->create(
            EmailCollectionFactory::class
        );
        $this->customTemplateVars = $customTemplateVars;

        $this->isMessageCreateAttachmentShouldBeUsed = !is_callable([$this->message, 'createAttachment']);
        if ($this->isMessageCreateAttachmentShouldBeUsed) {
            $this->message = $this->messageCreateAttachmentFactory->create();
        }
    }

    /**
     * Retrieve recipients
     *
     * @return string
     */
    public function getTo()
    {
        $recipients = $this->recipients;
        return reset($recipients);
    }

    /**
     * Retrieve template variables
     *
     * @return array
     */
    public function getTemplateVars()
    {
        return $this->templateVars;
    }

    /**
     * @return \Magento\Framework\Mail\Message|\Magento\Framework\Mail\MessageInterface
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * @param string $attachment
     * @param string $type
     * @param string $disposition
     * @param string $encoding
     * @param string $name
     * @return $this
     */
    public function createAttachment(
        $attachment,
        $type,
        $disposition,
        $encoding,
        $name
    ) {
        $this->message->createAttachment(
            $attachment,
            $type,
            $disposition,
            $encoding,
            $name
        );
        return $this;
    }

    /**
     * Prepare custom template vars
     *
     * @return $this
     */
    public function prepareCustomTemplateVars()
    {
        $templateVars = $this->getTemplateVars();
        foreach ($this->customTemplateVars as $customTemplateVarObject) {
            if ($varObject = $this->_checkVarObject($customTemplateVarObject)) {
                if ($customTemplateVars = $varObject->getVars($this, $templateVars)) {
                    $templateVars += $customTemplateVars;
                }
            }
        }

        $this->setTemplateVars($templateVars);
        return $this;
    }

    /**
     * @param mixed $customTemplateVarObject
     * @return bool|CustomTemplateVarsInterface
     */
    protected function _checkVarObject($customTemplateVarObject)
    {
        if ($customTemplateVarObject instanceof CustomTemplateVarsInterface) {
            return $customTemplateVarObject;
        } elseif (is_array($customTemplateVarObject)) {
            if (empty($customTemplateVarObject['vars_model'])
                || empty($customTemplateVarObject['template_code'])
                || !($customTemplateVarObject['vars_model'] instanceof CustomTemplateVarsInterface)
                || !$this->_checkTemplate($customTemplateVarObject['template_code'])
            ) {
                return false;
            }

            return $customTemplateVarObject['vars_model'];
        }

        return false;
    }

    /**
     * @param string $templateCode
     * @return int
     */
    protected function _checkTemplate($templateCode)
    {
        /** @var \Magento\Email\Model\ResourceModel\Template\Collection $collection */
        $collection = $this->emailCollectionFactory->create();
        $collection->addFieldToFilter('template_id', $this->templateIdentifier);
        $collection->addFieldToFilter('template_code', $templateCode);

        return $collection->getSize();
    }

    /**
     * @return $this
     */
    protected function prepareMessage()
    {
        $this->prepareCustomTemplateVars();
        $return = parent::prepareMessage();
        if ($this->message instanceof \Digidirect\Utilities\Model\Mail\Message) {
            $this->message->setPartsToBody();
        }
        return $return;
    }

    /**
     * {@inheritdoc}
     */
    public function addTo($address, $name = '')
    {
        $this->recipients[] = $address;
        return parent::addTo($address, $name);
    }

    /**
     * Reset object state
     * @return $this
     */
    public function resetObjectState()
    {
        return $this->reset();
    }

    /**
     * Reset object state
     *
     * @return $this
     */
    protected function reset()
    {
        $return = parent::reset();
        if ($this->isMessageCreateAttachmentShouldBeUsed) {
            $this->message = $this->messageCreateAttachmentFactory->create();
        }
        return $return;
    }
}
