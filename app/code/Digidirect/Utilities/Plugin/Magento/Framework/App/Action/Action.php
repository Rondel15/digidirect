<?php
namespace Digidirect\Utilities\Plugin\Magento\Framework\App\Action;

use Magento\Framework\App\Action\Action as Subject;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\Response\Http;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Message\ManagerInterface as MessageManager;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Serialize\Serializer\Json;

class Action
{
    const AJAX_PARAM = 'isAjax';

    /**
     * @var ResultFactory
     */
    protected $resultFactory;

    /**
     * @var RequestInterface
     */
    protected $request;

    /**
     * @var \Magento\Framework\Message\ManagerInterface
     */
    protected $messageManager;

    /**
     * @var Json
     */
    protected $json;

    /**
     * Action constructor.
     * @param ResultFactory $resultFactory
     * @param RequestInterface $request
     * @param MessageManager $messageManager
     * @param Json $json
     */
    public function __construct(
        ResultFactory $resultFactory,
        RequestInterface $request,
        MessageManager $messageManager,
        Json $json = null
    ) {
        $this->resultFactory = $resultFactory;
        $this->request = $request;
        $this->messageManager = $messageManager;
        $this->json = $json ?: ObjectManager::getInstance()->get(Json::class);
    }

    /**
     * @param Subject $subject
     * @param \Magento\Framework\Controller\ResultInterface $result
     * @return \Magento\Framework\Controller\ResultInterface
     * @SuppressWarnings(PHPMD.ExitExpression)
     */
    public function afterDispatch(
        Subject $subject,
        $result
    ) {
        if ($this->request->getParam(static::AJAX_PARAM)) {
            $responseData = [];
            $messages = $this->messageManager->getMessages(true);
            foreach ($messages->getItems() as $item) {
                $responseData[$item->getType()][] = $item->getText();
            }

            if ($result instanceof Redirect) {
                /** @var \Magento\Framework\Controller\Result\Json $resultJson */
                $resultJson = $this->resultFactory->create(ResultFactory::TYPE_JSON);
                $resultJson->setData($responseData);
                return $resultJson;
            }

            $response = $subject->getResponse();
            if ($response instanceof Http && $response->isRedirect()) {
                $response->representJson($this->json->serialize($responseData));
                $response->clearHeaders();
                $response->sendResponse();

                /** @todo eliminate usage of exit statement */
                exit;
            }
        }
        return $result;
    }
}
