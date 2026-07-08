<?php
namespace  Digidirect\Blog\Ui\DataProvider\Post\Form\Modifier;

use Digidirect\Blog\Model\Post;
use Digidirect\Blog\Model\PostFactory;
use Magento\Ui\DataProvider\Modifier\ModifierInterface;

/**
 * Class Image
 */
class Image implements ModifierInterface
{
    /**
     * @var \Digidirect\Blog\Model\ImageProcessor
     */
    protected $imageProcessor;

    /**
     * @var PostFactory
     */
    protected $postFactory;

    /**
     * Image constructor.
     * @param \Digidirect\Blog\Model\ImageProcessor $imageProcessor
     * @param PostFactory $postFactory
     */
    public function __construct(
        \Digidirect\Blog\Model\ImageProcessor $imageProcessor,
        PostFactory $postFactory
    ) {
        $this->imageProcessor = $imageProcessor;
        $this->postFactory = $postFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function modifyMeta(array $meta)
    {
        return $meta;
    }

    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.UnusedLocalVariable)
     */
    public function modifyData(array $data)
    {
        /** @var Post $model */
        $model = $this->postFactory->create();
        foreach ($data as $id => &$itemData) {
            foreach ($itemData as $tab => &$tabData) {
                if (is_array($tabData)) {
                    foreach ($tabData as $field => &$value) {
                        if (in_array($field, $model->getImagesFields())) {
                            $value = $this->imageProcessor->getImageForUploader($value, $field, $id);
                        }
                    }
                }
            }
        }
        return $data;
    }
}
