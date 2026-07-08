<?php
namespace Digidirect\Blog\Model;

use Digidirect\Blog\Api\Data\ImageInterface;
use Magento\Framework\Image\Factory as ImageFactory;

/**
 * Class ImageProcessor
 */
class ImageProcessor
{
    const MIN_HEIGHT = 50;

    const MAX_HEIGHT = 1080;

    const MIN_WIDTH = 50;

    const MAX_WIDTH = 1920;

    /**
     * @var ImageUploader
     */
    protected $imageUploader;

    /**
     * @var \Magento\Framework\Filesystem\Io\File
     */
    protected $fileSystemDriver;

    /**
     * @var ImageFactory
     */
    protected $imageFactory;

    /**
     * ImageProcessor constructor.
     * @param ImageUploader $imageUploader
     * @param \Magento\Framework\Filesystem\Io\File $fileSystemDriver
     * @param ImageFactory $imageFactory
     */
    public function __construct(
        \Digidirect\Blog\Model\ImageUploader $imageUploader,
        \Magento\Framework\Filesystem\Io\File $fileSystemDriver,
        ImageFactory $imageFactory
    ) {
        $this->imageUploader = $imageUploader;
        $this->fileSystemDriver = $fileSystemDriver;
        $this->imageFactory = $imageFactory;
    }

    /**
     * @param array $files
     * @return \string[]
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function saveFileToTmpDir(array $files)
    {
        return $this->imageUploader->saveFileToTmpDir($files[key($files)]);
    }

    /**
     * @param ImageInterface $model
     * @return void
     */
    public function prepareImageToSave(ImageInterface $model)
    {
        foreach ($model->getImagesFields() as $imageField) {
            $imageData = $model->getData($imageField);
            if (is_array($imageData)) {
                foreach ($imageData as $image) {
                    $imageName = '';
                    if (!empty($image['name'])) {
                        $imageName = $this->imageUploader->generateImageName($image['name']);
                    } elseif (!empty($image['file'])) {
                        $imageName = $image['file'];
                    }
                    $model->setData($imageField, $imageName);
                }
                $model->setData($imageField . '_data', $imageData);
            } else {
                $model->setData($imageField, '');
            }
        }
    }

    /**
     * @param ImageInterface $model
     * @return void
     */
    public function saveImages(ImageInterface $model)
    {
        foreach ($model->getImagesFields() as $imageField) {
            $imageData = $model->getData($imageField . '_data');
            if (is_array($imageData)) {
                foreach ($imageData as $image) {
                    if (!empty($image['name'])) {
                        $this->processNewFile(
                            $image['name'],
                            $imageField,
                            $model->getId(),
                            $model->getData($imageField)
                        );
                    } else {
                        $this->processExistsFile($image);
                    }
                }
            } else {
                $this->imageUploader->removeImageDirectory($imageField, $model->getId());
            }
        }
    }
    
    /**
     * @param string $imageName
     * @param string $imageField
     * @param string $modelId
     * @param string $newImageName
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function processNewFile($imageName, $imageField, $modelId, $newImageName)
    {
        return $this->imageUploader->moveFileFromTmp($imageName, $imageField, $modelId, $newImageName);
    }

    /**
     * @param string $image
     * @return null|string
     */
    protected function processExistsFile($image)
    {
        return is_array($image) && !empty($image['file']) ? $image['file'] : null;
    }

    /**
     * @param string $imageName
     * @param string $imageType
     * @param string $modelId
     * @return array|null
     */
    public function getImageForUploader($imageName, $imageType, $modelId)
    {
        if (!empty($imageName)) {
            $path = $this->imageUploader->getImagePath($imageName, $imageType, $modelId);
            $file = $this->imageUploader->getMediaDirectory()->getAbsolutePath($path);
            if ($this->fileSystemDriver->fileExists($file)) {
                return [
                    [
                        'exists' => true,
                        'file' => $imageName,
                        'url' => $this->imageUploader->getWebUrl(
                            $this->imageUploader->getImagePath($imageName, $imageType, $modelId)
                        ),
                        'size' => $this->imageUploader->getMediaDirectory()->stat($path)['size'],
                        'type' => 'image'
                    ]
                ];
            }
        }
        return null;
    }

    /**
     * @param Post $item
     * @param string $imageType
     * @param null|string $width
     * @param null|string $height
     * @return bool|string
     * @throws \Exception
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function resizeImage(Post $item, $imageType, $width = null, $height = null)
    {
        $imageFile = $item->getData($imageType);

        if (!$imageFile) {
            return false;
        }

        if (!$width && !$height) {
            return $this->imageUploader->getWebUrl(
                $this->imageUploader->getImagePath($imageFile, $imageType, $item->getId())
            );
        }

        if ($height && !$width) {
            $path = $this->imageUploader->getImagePath($imageFile, $imageType, $item->getId());
            $file = $this->imageUploader->getMediaDirectory()->getAbsolutePath($path);
            if ($this->fileSystemDriver->fileExists($file)) {
                $stat = getimagesize($file);
                if (!empty($stat)) {
                    $width = round(($height / $stat[1])*$stat[0]);
                }
            }
        }

        if ($width < self::MIN_WIDTH || $width > self::MAX_WIDTH) {
            return false;
        }
        $width = (int)$width;

        if (!empty($height)) {
            if ($height < self::MIN_HEIGHT || $height > self::MAX_HEIGHT) {
                return false;
            }
            $height = (int)$height;
        }

        $cacheDir = $this->imageUploader->getBaseDir() . 'cache' . DIRECTORY_SEPARATOR . $width;
        $cacheUrl = $this->imageUploader->getBaseUrlMedia()
            . 'cache' . DIRECTORY_SEPARATOR . $width . DIRECTORY_SEPARATOR;
        $io = $this->fileSystemDriver;
        $io->checkAndCreateFolder($cacheDir);
        $io->open(['path' => $cacheDir]);
        if ($io->fileExists($imageFile)) {
            return $cacheUrl . $imageFile;
        }
        $imagePath = $this->imageUploader->getMediaDirectory()->getAbsolutePath(
            $this->imageUploader->getImagePath($imageFile, $imageType, $item->getId())
        );
        try {
            $image = $this->imageFactory->create($imagePath);
            $image->keepTransparency(true);
            $image->resize($width, $height);
            $image->save($cacheDir . DIRECTORY_SEPARATOR . $imageFile);
            return $cacheUrl . $imageFile;
        } catch (\Exception $e) {
            return false;
        }
    }
}
