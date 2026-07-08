<?php

namespace Digidirect\Feed\Export\Filter;

use Magento\Framework\Filesystem;
use Magento\Framework\App\Filesystem\DirectoryList;
use Digidirect\Feed\Helper\Image;
use Magento\Framework\DataObject;

class ImageFilter
{
    /**
     * @var Filesystem
     */
    protected $filesystem;

    /**
     * @var Image
     */
    protected $image;

    /**
     * @param Filesystem $filesystem
     * @param Image $image
     */
    public function __construct(
        Filesystem $filesystem,
        Image $image
    ) {
        $this->filesystem = $filesystem;
        $this->image = $image;
    }

    /**
     * Resize image
     *
     * @param string $input
     * @param int $width
     * @param int $height
     *
     * @return string
     */
    public function resize($input, $width = null, $height = null)
    {
        $media = $this->filesystem->getUri(DirectoryList::MEDIA);
        $paths = explode($media, $input);

        if (count($paths) == 2) {
            $image = $paths[1];

            return (string)$this->image->init(new DataObject(), null, null, $image)
                ->resize($width, $height);
        }

        return $input;
    }
}
