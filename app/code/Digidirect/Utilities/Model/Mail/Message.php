<?php

namespace Digidirect\Utilities\Model\Mail;

/**
 * Class Message
 * This class will be used for 2.2.8+ to have a method createAttachment and backward compatibility
 * Could not use \Magento\Framework\Mail\MailMessageInterface because of di:compile (interface is created in 2.2.8)
 *
 * @package Digidirect\Utilities\Model\Mail
 */
class Message implements \Magento\Framework\Mail\MessageInterface
{
    /**
     * @var \Zend\Mail\Message
     */
    private $zendMessage;

    /**
     * @var \Zend\Mime\Part[]
     */
    protected $parts = [];

    /**
     * Could not use in constructor Zend\Mime and Zend\Mail - there are no such packages in 2.2.7-.
     *
     * @param string $charset
     */
    public function __construct(
        $charset = 'utf-8'
    ) {
        $this->zendMessage = \Zend\Mail\MessageFactory::getInstance();
        $this->zendMessage->setEncoding($charset);
    }

    /**
     * Add the HTML mime part to the message.
     *
     * @param string $content
     * @return $this
     */
    public function setBodyText($content)
    {
        $textPart = new \Zend\Mime\Part();

        $textPart->setContent($content)
            ->setType(\Zend\Mime\Mime::TYPE_TEXT)
            ->setCharset($this->zendMessage->getEncoding());

        $this->parts[] = $textPart;

        return $this;
    }

    /**
     * Add the text mime part to the message.
     *
     * @param string $content
     * @return $this
     */
    public function setBodyHtml($content)
    {
        $htmlPart = new \Zend\Mime\Part();

        $htmlPart->setContent($content)
            ->setType(\Zend\Mime\Mime::TYPE_HTML)
            ->setCharset($this->zendMessage->getEncoding());

        $this->parts[] = $htmlPart;

        return $this;
    }

    /**
     * Add the attachment mime part to the message.
     *
     * @param string $content
     * @param string $fileName
     * @param string $fileType
     * @return $this
     */
    public function setBodyAttachment($content, $fileName, $fileType)
    {
        $attachmentPart = new \Zend\Mime\Part();

        $attachmentPart->setContent($content)
            ->setType($fileType)
            ->setFileName($fileName)
            ->setDisposition(\Zend\Mime\Mime::DISPOSITION_ATTACHMENT)
            ->setEncoding(\Zend\Mime\Mime::ENCODING_BASE64);

        $this->parts[] = $attachmentPart;

        return $this;
    }

    /**
     * Set parts to Zend message body.
     *
     * @return $this
     */
    public function setPartsToBody()
    {
        $mimeMessage = new \Zend\Mime\Message();
        $mimeMessage->setParts($this->parts);
        $this->zendMessage->setBody($mimeMessage);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setBody($body)
    {
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setSubject($subject)
    {
        $this->zendMessage->setSubject($subject);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getSubject()
    {
        return $this->zendMessage->getSubject();
    }

    /**
     * {@inheritdoc}
     */
    public function getBody()
    {
        return $this->zendMessage->getBody();
    }

    /**
     * {@inheritdoc}
     */
    public function setFrom($fromAddress)
    {
        $this->zendMessage->setFrom($fromAddress);

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function setFromAddress($fromAddress, $fromName = null)
    {
        $this->zendMessage->setFrom($fromAddress, $fromName);
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function addTo($toAddress)
    {
        $this->zendMessage->addTo($toAddress);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function addCc($ccAddress)
    {
        $this->zendMessage->addCc($ccAddress);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function addBcc($bccAddress)
    {
        $this->zendMessage->addBcc($bccAddress);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setReplyTo($replyToAddress)
    {
        $this->zendMessage->setReplyTo($replyToAddress);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getRawMessage()
    {
        return $this->zendMessage->toString();
    }

    /**
     * @inheritDoc
     */
    public function setMessageType($type)
    {
        return $this;
    }

    /**
     * Method is created for the backward compatibility
     *
     * @param string $body
     * @param string $mimeType
     * @param string $disposition
     * @param string $encoding
     * @param null|string $filename
     * @return $this
     */
    public function createAttachment(
        $body,
        $mimeType = \Zend\Mime\Mime::TYPE_OCTETSTREAM,
        $disposition = \Zend\Mime\Mime::DISPOSITION_ATTACHMENT,
        $encoding = \Zend\Mime\Mime::ENCODING_BASE64,
        $filename = null
    ) {
        $attachmentPart = new \Zend\Mime\Part();

        $attachmentPart->setContent($body)
            ->setType($mimeType)
            ->setFileName($filename)
            ->setDisposition($disposition)
            ->setEncoding($encoding);

        $this->parts[] = $attachmentPart;

        return $this;
    }
}
