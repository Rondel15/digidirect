<?php
namespace Digidirect\Utilities\Plugin\Magento\Config\Model\Config\Backend;

class Image
{
    /**
     * Http Request Object
     * @var \Magento\Framework\App\Request\Http
     */
    protected $_request;

    /**
     * Image constructor.
     * @param \Magento\Framework\App\Request\Http $request
     */
    public function __construct(\Magento\Framework\App\Request\Http $request)
    {
        $this->_request = $request;
    }

    /**
     * Before save plugin
     * @param \Magento\Config\Model\Config\Backend\Image $subject
     * @param null $image
     * @return null
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function beforeBeforeSave(\Magento\Config\Model\Config\Backend\Image $subject, $image = null)
    {
        if ($subject->getPath() == 'design/loading_animation/image') {
            $groups = $this->_request->getParam('groups');
            $loadingAnimationGroup = $this->_getValue($groups, 'loading_animation');
            $loadingAnimationFields = $this->_getValue($loadingAnimationGroup, 'fields');
            if (!$this->_getValue($loadingAnimationFields, 'enable')) {
                return $image;
            }

            $value = $subject->getValue();
            $uploadedName = $this->_getValue($value, 'name');
            $uploadedValue = $this->_getValue($value, 'value');
            $oldValue = $subject->getOldValue();
            $delete = $this->_getValue($value, 'delete');

            $this->_processNeedUploadFirstImage($delete, $uploadedName, $oldValue);
            $this->_processNeedUploadNewImageAfterRemoveImage($delete, $uploadedName);
            $this->_processSaveWithoutImage($delete, $uploadedValue, $oldValue);
        }

        return $image;
    }

    /**
     * Case when enable la first time
     * @param string $delete
     * @param string $uploadedName
     * @param string $oldValue
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _processNeedUploadFirstImage($delete, $uploadedName, $oldValue)
    {
        if (!$delete && !$uploadedName && !$oldValue) {
            throw new \Magento\Framework\Exception\LocalizedException(
                __('You have not uploaded any images yet')
            );
        }
    }

    /**
     * Case when click save with enabled animation and without image uploaded
     * @param string $delete
     * @param string $uploadedValue
     * @param string $oldValue
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException
     */

    protected function _processSaveWithoutImage($delete, $uploadedValue, $oldValue)
    {
        if (!$delete && !$uploadedValue && $oldValue) {
            throw new \Magento\Framework\Exception\LocalizedException(
                __('Please upload an image ')
            );
        }
    }

    /**
     * Case when click delete image and did non upload new
     * @param string $delete
     * @param string $uploadedValue
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _processNeedUploadNewImageAfterRemoveImage($delete, $uploadedValue)
    {
        if ($delete && !$uploadedValue) {
            throw new \Magento\Framework\Exception\LocalizedException(
                __('You have to upload new image when remove the old one')
            );
        }
    }

    /**
     * Get value if isset key or null
     * @param [] $array
     * @param string $key
     * @return [] | null
     */
    protected function _getValue($array, $key)
    {
        return isset($array[$key]) ? $array[$key] : null;
    }
}
