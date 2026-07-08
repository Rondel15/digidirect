<?php
namespace Digidirect\AbstractEntity\Ui\DataProvider\Form\Modifier;

use Digidirect\AbstractEntity\Helper\Image as ImageHelper;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Registry;

class Images extends AbstractModifier
{
    /**
     * @var ImageHelper
     */
    protected $imageHelper;

    /**
     * Images constructor.
     * @param Registry $registry
     * @param RequestInterface $request
     * @param ImageHelper $imageHelper
     */
    public function __construct(
        Registry $registry,
        RequestInterface $request,
        ImageHelper $imageHelper
    ) {
        $this->imageHelper = $imageHelper;
        parent::__construct(
            $registry,
            $request
        );
    }

    /**
     * {@inheritdoc}
     */
    public function modifyData(array $data)
    {
        return $data;
    }

    /**
     * {@inheritdoc}
     */
    public function modifyMeta(array $meta)
    {
        foreach ($meta as &$fieldSet) {
            foreach ($fieldSet['children'] as &$field) {
                if ($field['arguments']['data']['config']['dataType'] == 'image') {
                    $field['arguments']['data']['config']['disabled'] = false;
                    $field['arguments']['data']['config']['formElement'] = 'fileUploader';
                    $field['arguments']['data']['config']['componentType'] = 'fileUploader';
                    $field['arguments']['data']['config']['allowedExtensions'] = 'jpg jpeg gif png svg';
                    $field['arguments']['data']['config']['uploaderConfig']['url'] =
                        'digidirect_abstractentity/abstractentity/imageUpload';
                    $field['arguments']['data']['config']['notice'] =
                        __('Allowed file types: jpg, jpeg, gif, png.');
                }
            }
        }
        return $meta;
    }
}
