<?php

namespace Digidirect\Vii\Model;

use Digidirect\Vii\Helper\Data;
use Magento\Backend\App\Area\FrontNameResolver;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Translate\Inline\StateInterface;
use Magento\Framework\Mail\Template\TransportBuilder;
use Magento\Store\Model\Store;

class Mail
{
    /**
     * Scope Config Interface
     *
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * Inline Translation
     *
     * @var \Magento\Framework\Translate\Inline\StateInterface
     */
    protected $inlineTranslation;

    /**
     * Transport Builder
     *
     * @var \Digidirect\AI\Model\Magento\Framework\Mail\Template\TransportBuilder
     */
    protected $transportBuilder;

    /**
     * @var Data
     */
    protected $helper;

    /**
     * Mail constructor.
     * @param Context $context
     * @param StateInterface $inlineTranslation
     * @param TransportBuilder $transportBuilder
     * @param Data $helper
     */
    public function __construct(
        Context $context,
        StateInterface $inlineTranslation,
        TransportBuilder $transportBuilder,
        Data $helper
    ) {
        $this->scopeConfig = $context->getScopeConfig();
        $this->inlineTranslation = $inlineTranslation;
        $this->transportBuilder = $transportBuilder;
        $this->helper = $helper;
    }

    /**
     * @param mixed $recipientsList
     * @param array $emailVars
     * @param int $storeId
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\MailException
     */
    public function sendOrderCancelEmail($recipientsList, $emailVars, $storeId)
    {
        $storeId = $storeId ?? Store::DEFAULT_STORE_ID;
        $senderInfo = $this->helper->getOrderCancelEmailSender($storeId);
        $templateId = $this->helper->getOrderCancelEmailTemplate($storeId);
        $this->sendEmail($templateId, $recipientsList, $senderInfo, $emailVars);
    }

    /**
     * @param string $templateId
     * @param array|string $receiverInfo
     * @param array|string $senderInfo
     * @param array $emailTemplateVariables
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\MailException
     */
    public function sendEmail($templateId, $receiverInfo, $senderInfo, array $emailTemplateVariables)
    {
        $this->inlineTranslation->suspend();

        $this->transportBuilder->setTemplateIdentifier($templateId)
            ->setTemplateOptions(
                [
                    'area' => FrontNameResolver::AREA_CODE,
                    'store' => Store::DEFAULT_STORE_ID,
                ]
            )
            ->setTemplateVars($emailTemplateVariables)
            ->setFromByScope($senderInfo)
            ->addTo($receiverInfo);

        $transport = $this->transportBuilder->getTransport();
        $transport->sendMessage();
        $this->inlineTranslation->resume();
    }
}
