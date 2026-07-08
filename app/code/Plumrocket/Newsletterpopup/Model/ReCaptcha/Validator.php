<?php
/**
 * @package     Plumrocket_Newsletterpopup
 * @copyright   Copyright (c) 2021 Plumrocket Inc. (https://plumrocket.com)
 * @license     https://plumrocket.com/license   End-user License Agreement
 */

declare(strict_types=1);

namespace Plumrocket\Newsletterpopup\Model\ReCaptcha;

use Magento\Framework\App\Helper\Context;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\HTTP\ClientFactory;
use Magento\Framework\HTTP\PhpEnvironment\RemoteAddress;
use Magento\Framework\Module\Manager;
use Magento\Framework\ObjectManagerInterface;
use Plumrocket\Newsletterpopup\Block\Popup\Fields\Recaptcha as RecaptchaField;
use Plumrocket\Newsletterpopup\Helper\Config as ConfigHelper;
use Plumrocket\Newsletterpopup\Model\Config\Source\ReCaptcha;

/**
 * @since 4.1.3
 */
class Validator
{
    const RECAPTCHA_REQUEST_URL = 'https://www.google.com/recaptcha/api/siteverify?';

    /**
     * @var \Magento\ReCaptchaUi\Model\ValidationConfigResolverInterface|null
     */
    private $validationConfigResolver = null;

    /**
     * @var \Magento\ReCaptchaUi\Model\CaptchaResponseResolverInterface|null
     */
    private $captchaResponseResolver = null;

    /**
     * @var \Magento\ReCaptchaValidationApi\Api\ValidatorInterface|null
     */
    private $captchaValidator = null;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    private $request;

    /**
     * @var \Magento\Framework\HTTP\Client\ClientFactory
     */
    private $curlClientFactory;

    /**
     * @var \Magento\Framework\HTTP\PhpEnvironment\RemoteAddress
     */
    private $remoteAddress;

    /**
     * @var \Plumrocket\Newsletterpopup\Helper\Config
     */
    private $configHelper;

    /**
     * @param \Magento\Framework\App\RequestInterface $request
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Magento\Framework\HTTP\ClientFactory $curlClientFactory
     * @param \Magento\Framework\HTTP\PhpEnvironment\RemoteAddress $remoteAddress
     * @param \Magento\Framework\Module\Manager $moduleManager
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param \Plumrocket\Newsletterpopup\Helper\Config $configHelper
     */
    public function __construct(
        RequestInterface $request,
        Context $context,
        ClientFactory $curlClientFactory,
        RemoteAddress $remoteAddress,
        Manager $moduleManager,
        ObjectManagerInterface $objectManager,
        ConfigHelper $configHelper
    ) {
        if ($moduleManager->isEnabled('Magento_ReCaptchaUi')) {
            $this->validationConfigResolver = $objectManager->get('Magento\ReCaptchaUi\Model\ValidationConfigResolverInterface');
            $this->captchaResponseResolver = $objectManager->get('Magento\ReCaptchaUi\Model\CaptchaResponseResolverInterface');
            $this->captchaValidator = $objectManager->get('Magento\ReCaptchaValidationApi\Api\ValidatorInterface');
        }

        $this->request = $request;
        $this->logger = $context->getLogger();
        $this->curlClientFactory = $curlClientFactory;
        $this->remoteAddress = $remoteAddress;
        $this->configHelper = $configHelper;
    }

    /**
     * @param $recaptchaResponse
     * @return bool
     * @throws \Magento\Framework\Exception\InputException
     */
    public function isValid($recaptchaResponse): bool
    {
        // Validate Magento 2.4.* default ReCaptcha
        if (ReCaptcha::DEFAULT_CONFIG === $this->configHelper->getReCaptchaConfigType()) {
            return $this->validateUiReCaptcha();
        }

        $params = [
            'secret'    => $this->configHelper->getReCaptchaSecretKey(),
            'response'  => $recaptchaResponse,
            'remoteip'  => $this->remoteAddress->getRemoteAddress()
        ];

        $requestURL = self::RECAPTCHA_REQUEST_URL . http_build_query($params);
        $response = $this->sendCurlRequest($requestURL);

        if ($response) {
            $response = json_decode($response, true);
        } else {
            return false;
        }

        return !empty($response['success']);
    }

    /**
     * Validate ReCaptchaUi (this type of ReCaptcha is supported by default for Magento 2.4.*)
     *
     * @return bool
     * @throws \Magento\Framework\Exception\InputException
     */
    private function validateUiReCaptcha(): bool
    {
        try {
            $reCaptchaResponse = $this->captchaResponseResolver->resolve($this->request);
        } catch (\Magento\Framework\Exception\InputException $e) {
             return false;
        }

        $validationConfig = $this->validationConfigResolver->get(RecaptchaField::RECAPTCHA_KEY);
        $validationResult = $this->captchaValidator->isValid($reCaptchaResponse, $validationConfig);

        return $validationResult->isValid();
    }

    /**
     * @param      $uri
     * @param null $params
     * @return string|false
     */
    protected function sendCurlRequest($uri, $params = null)
    {
        try {
            $curlClient = $this->curlClientFactory->create();
            if (empty($params)) {
                $curlClient->get($uri);
            } else {
                $curlClient->post($uri, $params);
            }

            return $curlClient->getBody();
        } catch (\Exception $e) {
            $this->logger->critical($e->getMessage());
        }

        return false;
    }
}
