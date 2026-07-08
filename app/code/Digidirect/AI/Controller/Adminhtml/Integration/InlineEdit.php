<?php
namespace Digidirect\AI\Controller\Adminhtml\Integration;

use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;

/**
 * Class InlineEdit
 *
 * @package Digidirect\AI\Controller\Adminhtml\Integration
 */
class InlineEdit extends \Magento\Backend\App\Action
{
    /**
     * @var JsonFactory
     */
    protected $jsonFactory;

    /**
     * @var \Digidirect\AI\Helper\Engine
     */
    protected $_engineHelper;

    /**
     * @var \Digidirect\AI\Model\Integrations\IntegrationsFactory
     */
    protected $_integrationFactory;

    /**
     * @var array|\Psr\Log\LoggerInterface
     */
    protected $_mLogger;

    /**
     * InlineEdit constructor.
     *
     * @param Context $context
     * @param JsonFactory $jsonFactory
     * @param \Digidirect\AI\Helper\Engine $engineHelper
     * @param \Digidirect\AI\Model\Integrations\IntegrationsFactory $integrationsFactory
     * @param \Psr\Log\LoggerInterface $mLogger
     */
    public function __construct(
        Context $context,
        JsonFactory $jsonFactory,
        \Digidirect\AI\Helper\Engine $engineHelper,
        \Digidirect\AI\Model\Integrations\IntegrationsFactory $integrationsFactory,
        \Psr\Log\LoggerInterface $mLogger
    ) {
        parent::__construct($context);
        $this->jsonFactory = $jsonFactory;
        $this->_engineHelper = $engineHelper;
        $this->_integrationFactory = $integrationsFactory;
        $this->_mLogger = $mLogger;
    }

    /**
     * Dispatch request
     *
     * @return \Magento\Framework\Controller\ResultInterface
     * @throws \Magento\Framework\Exception\NotFoundException
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function execute()
    {
        /** @var \Magento\Framework\Controller\Result\Json $resultJson */
        $resultJson = $this->jsonFactory->create();
        $error = false;
        $messages = [];

        $postItems = $this->getRequest()->getParam('items', []);
        if (!($this->getRequest()->getParam('isAjax') && count($postItems))) {
            return $resultJson->setData([
                'messages' => [__('Please correct the data sent.')],
                'error' => true,
            ]);
        }

        foreach (array_keys($postItems) as $integrationId) {
            try {
                $data = $postItems[$integrationId];
                $integration = $this->_integrationFactory->create();
                $integration->getResource()->load($integration, $integrationId, 'process_code');

                if (!$integration->getProcessCode()) {
                    $integration->setProcessCode($data['process_code']);
                }

                if ($data['status'] == \Digidirect\AI\Model\Integrations\Integrations::STATUS_PENDING) {
                    $integration->setStatus(\Digidirect\AI\Model\Integrations\Integrations::STATUS_PENDING);
                }

                if ($data['status'] == \Digidirect\AI\Model\Integrations\Integrations::STATUS_DISABLED) {
                    $integration->setStatus(\Digidirect\AI\Model\Integrations\Integrations::STATUS_DISABLED);
                }

                if (!isset($data['child_process_code'])) {
                    $data['child_process_code'] = '';
                }

                $validation = $this->_engineHelper->validateChainInfinity(
                    $integration->getProcessCode(),
                    $data['child_process_code']
                );
                if ($validation->getIsInfinity()) {
                    $messages[] = __($validation->getStack());
                    $error = true;
                    break;
                }

                if (isset($data['cron_time'])) {
                    if (!preg_match(\Digidirect\AI\Helper\Data::CRON_REGEXP, $data['cron_time'])
                        && !empty($data['cron_time'])
                    ) {
                        $messages[] = __('Cron time is not valid');
                        $error = true;
                        break;
                    }
                    $integration->setCronTime(trim($data['cron_time']));
                }

                $integration->setChildProcessCode($data['child_process_code']);
                $integration->getResource()->save($integration);
            } catch (\Throwable $e) {
                $messages[] = __('Something went wrong while saving the integration.');
                $this->_mLogger->error($e->getMessage());
                $error = true;
            }
        }

        return $resultJson->setData([
            'messages' => $messages,
            'error' => $error,
        ]);
    }
}
