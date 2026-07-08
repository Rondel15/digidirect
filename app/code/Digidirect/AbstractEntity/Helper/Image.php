<?php
namespace Digidirect\AbstractEntity\Helper;

use Digidirect\AbstractEntity\Api\Data\AbstractEntityInterface;
use Digidirect\AbstractEntity\Model\AbstractEntity;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\DataObject;
use Magento\Eav\Api\AttributeRepositoryInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Eav\Api\Data\AttributeInterface;

class Image extends \Digidirect\Utilities\Helper\Image
{
    const IMAGE_ID = 'image';

    /**
     * @var \Digidirect\AbstractEntity\Model\AbstractEntity\Media\Config
     */
    protected $mediaConfig;

    /**
     * @var \Magento\Framework\Filesystem
     */
    protected $filesystem;

    /**
     * @var \Magento\Framework\Filesystem\Directory\WriteInterface
     */
    protected $mediaDirectory;

    /**
     * @var \Magento\Framework\File\Mime
     */
    protected $mime;

    /**
     * @var array
     */
    protected $images = [];

    /**
     * @var SearchCriteriaBuilder
     */
    protected $searchCriteriaBuilder;

    /**
     * @var AttributeRepositoryInterface
     */
    protected $attributeRepository;

    /**
     * @var array
     */
    protected $frontendInput;

    /**
     * @var array
     */
    protected $aeImagesAttributes;

    /**
     * Image constructor.
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Digidirect\Utilities\Model\ImageFactory $imageFactory
     * @param \Magento\Framework\View\Asset\Repository $assetRepo
     * @param \Magento\Framework\View\ConfigInterface $viewConfig
     * @param \Digidirect\Utilities\Model\Media\ConfigInterface $mediaConfig
     * @param \Magento\Framework\Filesystem $filesystem
     * @param \Magento\Framework\File\Mime $mime
     * @param SearchCriteriaBuilder $searchCriteriaBuilder,
     * @param AttributeRepositoryInterface $attributeRepository
     * @param array $frontendInput
     * @param array $images
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Digidirect\Utilities\Model\ImageFactory $imageFactory,
        \Magento\Framework\View\Asset\Repository $assetRepo,
        \Magento\Framework\View\ConfigInterface $viewConfig,
        \Digidirect\Utilities\Model\Media\ConfigInterface $mediaConfig,
        \Magento\Framework\Filesystem $filesystem,
        \Magento\Framework\File\Mime $mime,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        AttributeRepositoryInterface $attributeRepository,
        array $frontendInput = [self::IMAGE_ID],
        array $images = [self::IMAGE_ID]
    ) {
        $this->mediaConfig = $mediaConfig;
        $this->filesystem = $filesystem;
        $this->mediaDirectory = $filesystem->getDirectoryWrite(DirectoryList::MEDIA);
        $this->mime = $mime;
        $this->images = $images;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->attributeRepository = $attributeRepository;
        $this->frontendInput = $frontendInput;
        parent::__construct($context, $imageFactory, $assetRepo, $viewConfig);
    }

    /**
     * Initialize Helper to work with Image
     * @param AbstractEntityInterface|\Magento\Framework\DataObject $entity
     * @param string $imageId
     * @param array $attributes
     * @return $this
     */
    public function init($entity, $imageId, $attributes = [])
    {
        parent::init($entity, 'digidirect_abstractentity_default', $attributes);
        $this->setImageFile($this->getAbstractEntityImage($entity, $imageId));
        return $this;
    }

    /**
     * @return array
     */
    public function getImages()
    {
        return array_unique(array_merge($this->images, $this->getImageAttributes()));
    }

    /**
     * Get image url from object
     * @param DataObject $entity
     * @param string $key
     * @return string
     */
    public function getOriginalImageUrl($entity = null, $key = self::IMAGE_ID)
    {
        return $this->mediaConfig->getBaseMediaUrl() . $this->getAbstractEntityImage($entity, $key);
    }

    /**
     * Get image from object
     * @param DataObject|null $entity
     * @param string $key
     * @return string
     */
    public function getAbstractEntityImage(DataObject $entity = null, $key = self::IMAGE_ID)
    {
        if (null === $entity) {
            $entity = $this->getEntity();
        }
        return $entity->getData($key);
    }

    /**
     * Get image information
     * @param DataObject|array $entity
     * @param string $key
     * @param bool $delete
     * @return \string[]
     */
    public function getImageInfo($entity, $key = self::IMAGE_ID, $delete = true)
    {
        $imageInfo = [];
        if ($entity instanceof DataObject) {
            $imageInfo = $entity->getData($key);
            if ($delete) {
                $entity->unsetData($key);
            }
        } elseif (is_array($entity) && isset($entity[$key])) {
            $imageInfo = $entity[$key];
            if ($delete) {
                unset($entity[$key]);
            }
        }

        if (is_array($imageInfo) && !empty($imageInfo)) {
            return current($imageInfo);
        }

        return [];
    }

    /**
     * @param DataObject|array|string $entity
     * @param string $key
     * @return bool
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    public function deleteOldImage($entity, $key = self::IMAGE_ID)
    {
        if ($entity instanceof DataObject) {
            $images = $entity->getData($key);
        } elseif (is_array($entity)) {
            $images = !empty($entity[$key]) ? $entity[$key] : $entity;
        } else {
            $images = strval($entity);
        }

        if (!empty($images)) {
            if (!is_array($images)) {
                $images = [$images];
            }

            foreach ($images as $image) {
                if (!$image) {
                    continue;
                }
                $fileName = $this->mediaConfig->getBaseMediaPath() . $image;
                $this->mediaDirectory->delete($fileName);
            }

            return true;
        }

        return false;
    }

    /**
     * Get image information
     * @param DataObject $entity
     * @return DataObject
     */
    public function addImagesInfo(DataObject $entity)
    {
        foreach ($this->getImages() as $image) {
            $this->addImageInfo($entity, $image);
        }
        return $entity;
    }

    /**
     * Get image information
     * @param DataObject $entity
     * @param string $key
     * @return DataObject
     */
    protected function addImageInfo(DataObject $entity, $key = self::IMAGE_ID)
    {
        if ($image = $entity->getData($key)) {
            $fileName = $this->mediaConfig->getBaseMediaPath() . $image;
        }

        if (isset($fileName) && $this->mediaDirectory->isExist($fileName)) {
            $stat = $this->mediaDirectory->stat($fileName);
            $imageInfo = [
                [
                    'url'    => $this->getOriginalImageUrl($entity, $key),
                    'file'   => $image,
                    'size'   => is_array($stat) ? $stat['size'] : 0,
                    'exists' => true,
                    'type' => $this->mime->getMimeType($this->mediaDirectory->getAbsolutePath($fileName)),
                ]
            ];
            $entity->setData($key, $imageInfo);
        } else {
            $entity->unsetData($key);
        }

        return $entity;
    }

    /**
     * @return array
     */
    protected function getImageAttributes()
    {
        if (null === $this->aeImagesAttributes && !empty($this->frontendInput)) {
            $this->aeImagesAttributes = [];
            $this->searchCriteriaBuilder->addFilter(AttributeInterface::FRONTEND_INPUT, $this->frontendInput, 'in');
            $searchCriteria = $this->searchCriteriaBuilder->create();
            $attributes = $this->attributeRepository->getList(AbstractEntity::ENTITY_TYPE, $searchCriteria);
            foreach ($attributes->getItems() as $item) {
                $this->aeImagesAttributes[] = $item->getAttributeCode();
            }
        }

        return $this->aeImagesAttributes;
    }
}
