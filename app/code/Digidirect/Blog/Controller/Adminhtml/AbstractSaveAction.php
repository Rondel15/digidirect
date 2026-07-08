<?php
namespace Digidirect\Blog\Controller\Adminhtml;

use Magento\Framework\Model\AbstractModel;

/**
 * Class AbstractSaveAction
 */
abstract class AbstractSaveAction extends \Magento\Backend\App\Action
{
    /**
     * @param AbstractModel $model
     * @param array $postData
     * @return void
     */
    protected function extractPostData(AbstractModel $model, $postData)
    {
        foreach ($postData as $value) {
            if (is_array($value)) {
                $model->addData($value);
            }
        }
    }

    /**
     * @param string $entity
     * @return int
     */
    protected function getId($entity)
    {
        $postData = $this->getRequest()->getPostValue();
        return isset($postData[$entity], $postData[$entity]['entity_id']) ? $postData[$entity]['entity_id'] : 0;
    }
}
