<?php
/**
 * @package     Plumrocket_Newsletterpopup
 * @copyright   Copyright (c) 2020 Plumrocket Inc. (https://plumrocket.com)
 * @license     https://plumrocket.com/license   End-user License Agreement
 */

namespace Plumrocket\Newsletterpopup\Controller\Index;

use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Action\Action;
use Magento\Framework\Message\MessageInterface;
use Magento\Framework\Registry;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Framework\Validator\EmailAddress as EmailValidator;
use Magento\Framework\Validator\Exception as ValidatorException;
use Plumrocket\Base\Api\ConfigUtilsInterface;
use Plumrocket\Newsletterpopup\Block\Popup\Fields\Dob;
use Plumrocket\Newsletterpopup\Helper\Config;
use Plumrocket\Newsletterpopup\Helper\Data;
use Plumrocket\Newsletterpopup\Model\ReCaptcha\Validator;
use Plumrocket\Newsletterpopup\Model\Subscriber;
use Psr\Log\LoggerInterface;

class Subscribe extends Action
{
    /**
     * @var \Plumrocket\Newsletterpopup\Model\ReCaptcha\Validator
     */
    private $reCaptchaValidator;

    /**
     * @var \Plumrocket\Newsletterpopup\Model\Subscriber
     */
    private $subscriber;

    /**
     * @var \Plumrocket\Newsletterpopup\Helper\Data
     */
    private $dataHelper;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    /**
     * @var \Magento\Framework\Serialize\SerializerInterface
     */
    private $serializer;

    /**
     * @var \Magento\Framework\Registry
     */
    private $coreRegistry;

    /**
     * @var \Magento\Framework\Validator\EmailAddress
     */
    private $emailValidator;

    /**
     * @var \Plumrocket\Newsletterpopup\Helper\Config
     */
    private $config;

    /**
     * @var \Plumrocket\Base\Api\ConfigUtilsInterface
     */
    private $configUtils;

    /**
     * @param \Magento\Backend\App\Action\Context                   $context
     * @param \Plumrocket\Newsletterpopup\Model\ReCaptcha\Validator $reCaptchaValidator
     * @param \Plumrocket\Newsletterpopup\Model\Subscriber          $subscriber
     * @param \Plumrocket\Newsletterpopup\Helper\Data               $dataHelper
     * @param \Psr\Log\LoggerInterface                              $logger
     * @param \Magento\Framework\Serialize\SerializerInterface      $serializer
     * @param \Magento\Framework\Registry                           $coreRegistry
     * @param \Magento\Framework\Validator\EmailAddress             $emailValidator
     * @param \Plumrocket\Newsletterpopup\Helper\Config             $config
     * @param \Plumrocket\Base\Api\ConfigUtilsInterface             $configUtils
     */
    public function __construct(
        Context $context,
        Validator $reCaptchaValidator,
        Subscriber $subscriber,
        Data $dataHelper,
        LoggerInterface $logger,
        SerializerInterface $serializer,
        Registry $coreRegistry,
        EmailValidator $emailValidator,
        Config $config,
        ConfigUtilsInterface $configUtils
    ) {
        parent::__construct($context);
        $this->reCaptchaValidator = $reCaptchaValidator;
        $this->subscriber = $subscriber;
        $this->dataHelper = $dataHelper;
        $this->logger = $logger;
        $this->serializer = $serializer;
        $this->coreRegistry = $coreRegistry;
        $this->emailValidator = $emailValidator;
        $this->config = $config;
        $this->configUtils = $configUtils;
    }

    /**
     * Subscribe the customer/guest.
     *
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface|void
     */
    public function execute()
    {
        try {
            if (! $this->config->isModuleEnabled()) {
                throw new ValidatorException(__('The Plumrocket Newsletter Popup Module is disabled.'));
            }

            $recaptchaResponse = $this->getRequest()->getParam('g-recaptcha-response');
            $popupId = $this->getRequest()->getParam('id');

            if (in_array('recaptcha', $this->dataHelper->getPopupFormFieldsKeys($popupId, true))
                && ! $this->reCaptchaValidator->isValid($recaptchaResponse)
            ) {
                throw new ValidatorException(__('reCAPTCHA verification failed.'));
            }

            $email = $this->getRequest()->getParam('email');
            if (! $this->emailValidator->isValid($email)) {
                throw new ValidatorException(__('Please enter a valid email address.'));
            }

            if ($this->configUtils->getStoreConfig('prnewsletterpopup/disposable_emails/disable')) {
                $_email = preg_replace('#[[:space:]]#', '', $email);
                preg_match('#@([\w\-.]+$)#is', $_email, $domain);
                if (!empty($domain[1])) {
                    preg_match(
                        '#(?:^|[\s,]+)'. preg_quote($domain[1]) . '(?:$|[\s,]+)#i',
                        $this->configUtils->getStoreConfig('prnewsletterpopup/disposable_emails/domains'),
                        $math
                    );
                    if (!empty($math)) {
                        throw new ValidatorException(
                            __(
                                'This email address provider is blocked. Please try again with different email address.'
                            )
                        );
                    }
                }
            }

            $subscriber = $this->subscriber->load($email, 'subscriber_email');
            if ((int)$subscriber->getId() !== 0) {
                throw new ValidatorException(__('This email address is already assigned to another user.'));
            }

            $inputData = $this->getRequest()->getPostValue();
            // Prepare DOB.
            if (empty($inputData['dob'])
                && !empty($inputData['month'])
                && !empty($inputData['day'])
                && !empty($inputData['year'])
            ) {
                $dateMapping = $this->_view
                    ->getLayout()
                    ->createBlock(Dob::class)
                    ->getDateMapping(false);
                $inputData['dob'] = sprintf(
                    $dateMapping,
                    (int) $inputData['month'],
                    (int) $inputData['day'],
                    (int) $inputData['year']
                );
            }

            // Prepare mailchimp lists if they was passed through integration data
            if (isset($inputData['integration']['mailchimp'])) {
                $inputData['mailchimp_list'] = $inputData['integration']['mailchimp'];
            }

            $subscriber->customSubscribe($email, $this, $inputData);
        } catch (ValidatorException $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
        } catch (\Exception $e) {
            // $this->messageManager->addError($e->getMessage());
            $this->messageManager->addErrorMessage(__('Unknown Error'));
            $this->logger->error(__METHOD__ . ' ' . $e->getMessage());
        }

        $data = [
            'error' => 0,
            'messages' => [],
            'hasSuccessTextPlaceholders' => false,
        ];

        $messages = $this->messageManager->getMessages(true);
        foreach ($messages->getItems() as $message) {
            if ($message->getType() !== MessageInterface::TYPE_SUCCESS) {
                $data['error'] = 1;
                if (! $this->coreRegistry->registry('prgdpr_skip_save_consents')) {
                    $this->coreRegistry->register('prgdpr_skip_save_consents', 1);
                }
            }
            if (!array_key_exists($message->getType(), $data['messages'])) {
                $data['messages'][$message->getType()] = [];
            }
            $data['messages'][$message->getType()][] = $message->getText();
        }

        if ($popupId = $this->getRequest()->getParam('id')) {
            $data['hasSuccessTextPlaceholders'] = $this->dataHelper
                ->getPopupById($popupId)
                ->hasSuccessTextPlaceholders();
        }

        $this->getResponse()
            ->setHeader('Content-type', 'application/json')
            ->clearHeader('Location')
            // ->clearRawHeader('Location')
            ->setHttpResponseCode(200)
            ->setBody($this->serializer->serialize($data));
    }
}
