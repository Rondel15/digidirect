<?php

namespace Digidirect\Blog\Controller\Comment;

use Digidirect\Blog\Api\CommentRepositoryInterface;
use Digidirect\Blog\Model\Comment\Source\Status;
use Digidirect\Blog\Model\CommentFactory;
use Magento\Framework\Exception\LocalizedException;
use Digidirect\Blog\Helper\Data;
use Magento\Customer\Model\Session;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Action\Action;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Framework\Data\Form\FormKey\Validator;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Save extends Action
{
    /**
     * @var Session
     */
    protected $customerSession;

    /**
     * @var Data
     */
    protected $dataHelper;

    /**
     * @var CommentFactory
     */
    protected $commentFactory;

    /**
     * @var CommentRepositoryInterface
     */
    protected $commentRepository;

    /**
     * @var DateTime
     */
    protected $dateTime;

    /**
     * @var Validator
     */
    protected $formKeyValidator;

    /**
     * Save constructor.
     *
     * @param Context $context
     * @param Data $dataHelper
     * @param CommentFactory $commentFactory
     * @param CommentRepositoryInterface $commentRepository
     * @param Session $customerSession
     * @param DateTime $dateTime
     * @param Validator $validator
     */
    public function __construct(
        Context $context,
        Data $dataHelper,
        CommentFactory $commentFactory,
        CommentRepositoryInterface $commentRepository,
        Session $customerSession,
        DateTime $dateTime,
        Validator $validator
    ) {
        $this->formKeyValidator = $validator;
        $this->dataHelper = $dataHelper;
        $this->commentFactory = $commentFactory;
        $this->commentRepository = $commentRepository;
        $this->customerSession = $customerSession;
        $this->dateTime = $dateTime;
        parent::__construct($context);
    }

    /**
     * @return $this
     * @throws \Exception
     */
    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        if (!$this->formKeyValidator->validate($this->getRequest())) {
            return $resultRedirect->setUrl($this->_redirect->getRefererUrl());
        }
        $data = $this->getRequest()->getPostValue();
        if (!empty($data)) {
            try {
                $comment = $this->commentFactory->create();
                $autoConfirm = $this->dataHelper->getCommentSettingsConfig('autoapprove');
                $loginApprove = $this->dataHelper->getCommentSettingsConfig('loginapprove');
                $loggedIn = $this->customerSession->isLoggedIn();
                if ($autoConfirm || ($loggedIn && $loginApprove)) {
                    $approveStatus = Status::STATUS_APPROVED;
                } else {
                    $approveStatus = Status::STATUS_DISAPPROVED;
                }
                $data['comment_date'] = $this->dateTime->date();
                $data['comment_status'] = $approveStatus;
                if (!$this->validatePost($data)) {
                    throw new \Exception();
                }
                $comment->setData($data);

                $this->commentRepository->save($comment);
                $this->messageManager->addSuccessMessage(__('The Comment has been posted.'));
            } catch (LocalizedException $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
            } catch (\RuntimeException $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
            } catch (\Exception $e) {
                $this->messageManager->addExceptionMessage($e, __('Something went wrong while saving the comment.'));
            }
        }
        return $resultRedirect->setUrl($this->_redirect->getRefererUrl());
    }

    /**
     * @param array $data
     * @return bool
     * @throws \Zend_Validate_Exception
     */
    protected function validatePost(array $data)
    {
        $error = false;
        if (!\Zend_Validate::is(trim($data['sender_name']), 'NotEmpty')) {
            $error = true;
        }
        if (!\Zend_Validate::is(trim($data['comment']), 'NotEmpty')) {
            $error = true;
        }
        if (!\Zend_Validate::is(trim($data['sender_email']), 'EmailAddress', ['domain' => false])) {
            $error = true;
        }
        return $error === false;
    }
}
