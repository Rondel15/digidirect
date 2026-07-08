<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Digidirect\Digi\Console\Command;

use Magento\Framework\App\Area;
use Magento\Framework\App\State;
use Magento\MediaStorage\Service\ImageResize;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Helper\ProgressBar;
use Magento\Framework\ObjectManagerInterface;
use Magento\Theme\Model\ResourceModel\Theme\Collection as ThemeCollection;
use \Magento\Catalog\Model\ResourceModel\Product\Image as ProductImage;
use Magento\Framework\Filesystem;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Catalog\Model\Product\Media\ConfigInterface as MediaConfig;
use Magento\Framework\Exception\NotFoundException;
use Magento\Framework\View\ConfigInterface as ViewConfig;
use Magento\Catalog\Helper\Image as ImageHelper;
use Magento\Theme\Model\Config\Customization as ThemeCustomizationConfig;
use Magento\Catalog\Model\Product\Image\ParamsBuilder;
use Magento\Catalog\Model\View\Asset\ImageFactory as AssertImageFactory;
use Magento\Framework\Image\Factory as ImageFactory;
use Magento\Framework\Image;

class ImagesResizeCommand extends \Symfony\Component\Console\Command\Command
{
    const THEME_NAME = 'theme_name';
    const SIZE_IMG = 'img_size';
    /**
     * @var ImageResize
     */
    private $resize;

    /**
     * @var State
     */
    private $appState;

    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;
    /**
     * @var ThemeCollection
     */
    private $themeCollection;

    /**
     * @var Filesystem\Directory\WriteInterface
     */
    private $mediaDirectory;
    /**
     * @var ProductImage
     */
    private $productImage;
    /**
     * @var MediaConfig
     */
    private $imageConfig;
    /**
     * @var ViewConfig
     */
    private $viewConfig;
    /**
     * @var ThemeCustomizationConfig
     */
    private $themeCustomizationConfig;
    /**
     * @var ParamsBuilder
     */
    private $paramsBuilder;
    /**
     * @var AssertImageFactory
     */
    private $assertImageFactory;
    /**
     * @var ImageFactory
     */
    private $imageFactory;

    /**
     * ImagesResizeCommand constructor.
     * @param State $appState
     * @param ImageResize $resize
     * @param ObjectManagerInterface $objectManager
     * @param ThemeCollection $themeCollection
     * @param Filesystem $filesystem
     * @param ProductImage $productImage
     * @param MediaConfig $imageConfig
     * @param ViewConfig $viewConfig
     * @param ThemeCustomizationConfig $themeCustomizationConfig
     * @param ParamsBuilder $paramsBuilder
     * @param AssertImageFactory $assertImageFactory
     * @param ImageFactory $imageFactory
     */
    public function __construct(
        State $appState,
        ImageResize $resize,
        ObjectManagerInterface $objectManager,
        ThemeCollection $themeCollection,
        Filesystem $filesystem,
        ProductImage $productImage,
        MediaConfig $imageConfig,
        ViewConfig $viewConfig,
        ThemeCustomizationConfig $themeCustomizationConfig,
        ParamsBuilder $paramsBuilder,
        AssertImageFactory $assertImageFactory,
        ImageFactory $imageFactory
    ) {
        parent::__construct();
        $this->resize = $resize;
        $this->appState = $appState;
        $this->objectManager = $objectManager;
        $this->themeCollection = $themeCollection;
        $this->mediaDirectory = $filesystem->getDirectoryWrite(DirectoryList::MEDIA);
        $this->productImage = $productImage;
        $this->imageConfig = $imageConfig;
        $this->viewConfig = $viewConfig;
        $this->themeCustomizationConfig = $themeCustomizationConfig;
        $this->paramsBuilder = $paramsBuilder;
        $this->assertImageFactory = $assertImageFactory;
        $this->imageFactory = $imageFactory;
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $arguments = [
            new InputOption(
                self::THEME_NAME,
                '-t',
                InputOption::VALUE_IS_ARRAY | InputOption::VALUE_OPTIONAL,
                'Code theme or Theme path, also can add multiple values via comma '
            ),
            new InputOption(
                self::SIZE_IMG,
                '-s',
                InputOption::VALUE_IS_ARRAY | InputOption::VALUE_OPTIONAL,
                'size image from format 100-100 also can add multiple values via comma '
            ),
        ];

        $this->setName('Digidirect:catalog:images:resize')
            ->setDescription('Creates resized product images using parameters -t Theme name, and -s size')
            ->setDefinition($arguments);
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        list($themeName, $imageSizes) = $this->getInputParam($input, $output);
        $selectedThemes = $this->themeCollection->addFieldToFilter('code', ['in' => $themeName])->getItems();
        try {
            $this->appState->setAreaCode(Area::AREA_GLOBAL);
            $generator = $this->resizeFromThemes($imageSizes, !empty($selectedThemes) ? $selectedThemes : null);

            /** @var ProgressBar $progress */
            $progress = $this->objectManager->create(ProgressBar::class, [
                'output' => $output,
                'max' => $generator->current()
            ]);
            $progress->setFormat(
                "%current%/%max% [%bar%] %percent:3s%% %elapsed% %memory:6s% \t| <info>%message%</info>"
            );

            if ($output->getVerbosity() !== OutputInterface::VERBOSITY_NORMAL) {
                $progress->setOverwrite(false);
            }

            for (; $generator->valid(); $generator->next()) {
                $progress->setMessage($generator->key());
                $progress->advance();
            }
        } catch (\Exception $e) {
            $output->writeln("<error>{$e->getMessage()}</error>");
            // we must have an exit code higher than zero to indicate something was wrong
            return \Magento\Framework\Console\Cli::RETURN_FAILURE;
        }

        $output->write(PHP_EOL);
        $output->writeln("<info>Product images resized successfully</info>");
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return mixed
     */
    private function getInputParam(InputInterface $input, OutputInterface $output)
    {
        $data = [];
        $data[] = $input->getOption(self::THEME_NAME);
        $data[] = $input->getOption(self::SIZE_IMG);

        try {
            $result = array_reduce($data, function ($acc, $option) use ($input) {
                $acc[] = !empty($option)
                    ? implode(' , ', $option)
                    : null;
                return $acc;
            }, []);
        } catch (\Exception $e) {
            $output->writeln($e->getMessage());
        }
        return $result;
    }

    /**
     * @param string $imageSize
     * @param array|null $themes
     * @return \Generator
     * @throws NotFoundException
     */
    public function resizeFromThemes(?string $imageSize, array $themes = null): \Generator
    {
        $count = $this->productImage->getCountAllProductImages();
        if (!$count) {
            throw new NotFoundException(__('Cannot resize images - product images not found'));
        }

        $productImages = $this->productImage->getAllProductImages();
        $viewImages = $this->getViewImages($themes ?? $this->getThemesInUse(), $imageSize);

        foreach ($productImages as $image) {
            $originalImageName = $image['filepath'];
            $originalImagePath = $this->mediaDirectory->getAbsolutePath(
                $this->imageConfig->getMediaPath($originalImageName)
            );
            foreach ($viewImages as $viewImage) {
                $this->resize($viewImage, $originalImagePath, $originalImageName);
            }
            yield $originalImageName => $count;
        }
    }

    /**
     * Get view images data from themes
     * @param array $themes
     * @param string $filterData
     * @return array
     */
    private function getViewImages(array $themes, ?string $filterData): array
    {
        $filter = $this->prepareFilter($filterData);
        $viewImages = [];
        /** @var \Magento\Theme\Model\Theme $theme */
        foreach ($themes as $theme) {
            $config = $this->viewConfig->getViewConfig([
                'area' => Area::AREA_FRONTEND,
                'themeModel' => $theme,
            ]);
            $images = $config->getMediaEntities('Magento_Catalog', ImageHelper::MEDIA_TYPE_CONFIG_NODE);
            $views = array_filter($images, function ($viewImage) use ($filter) {
                if (isset($viewImage['width']) && isset($viewImage['height']) && !empty($filter)) {
                    $isWidth = array_key_exists($viewImage['width'], $filter);
                    $isHeight = in_array($viewImage['height'], $filter);

                    return $isWidth && $isHeight;
                }
                return true;
            });
            foreach ($views as $imageId => $imageData) {
                $uniqIndex = $this->getUniqueImageIndex($imageData);
                $imageData['id'] = $imageId;
                $viewImages[$uniqIndex] = $imageData;
            }
        }
        return $viewImages;
    }

    /**
     * Get unique image index
     * @param array $imageData
     * @return string
     */
    private function getUniqueImageIndex(array $imageData): string
    {
        ksort($imageData);
        unset($imageData['type']);
        return md5(json_encode($imageData));
    }

    /**
     * @param string $filterData
     * @return array|mixed
     * @throws \Exception
     */
    private function prepareFilter(?string $filterData = '')
    {
        $result = [];
        if (!empty($filterData)) {
            try {
                $arrayFilter = explode(',', $filterData);
                $result = array_reduce($arrayFilter, function ($acc, $filter) {
                    list($width, $height) = explode('-', $filter);
                    $acc[$width] = $height;
                    return $acc;
         }, []);
            } catch (\Exception $e) {
                throw new \Exception('not correct set separator in size, please use sign "-" example 100-100');
            }
        }
        return $result;
    }

    /**
     * Search the current theme
     * @return array
     */
    private function getThemesInUse(): array
    {
        $themesInUse = [];
        $registeredThemes = $this->themeCollection->loadRegisteredThemes();
        $storesByThemes = $this->themeCustomizationConfig->getStoresByThemes();
        $keyType = is_integer(key($storesByThemes)) ? 'getId' : 'getCode';
        foreach ($registeredThemes as $registeredTheme) {
            if (array_key_exists($registeredTheme->$keyType(), $storesByThemes)) {
                $themesInUse[] = $registeredTheme;
            }
        }
        return $themesInUse;
    }

    /**
     * Resize image
     * @param array $viewImage
     * @param string $originalImagePath
     * @param string $originalImageName
     */
    private function resize(array $viewImage, string $originalImagePath, string $originalImageName)
    {
        $imageParams = $this->paramsBuilder->build($viewImage);
        $imageAsset = $this->assertImageFactory->create(
            [
                'miscParams' => $imageParams,
                'filePath' => $originalImageName,
            ]
        );

        if (!file_exists($imageAsset->getPath())) {
            $image = $this->makeImage($originalImagePath, $imageParams);

            if ($imageParams['image_width'] !== null && $imageParams['image_height'] !== null) {
                $image->resize($imageParams['image_width'], $imageParams['image_height']);
            }
            $image->save($imageAsset->getPath());
        }
    }

    /**
     * Make image
     * @param string $originalImagePath
     * @param array $imageParams
     * @return Image
     */
    private function makeImage(string $originalImagePath, array $imageParams): Image
    {
        $image = $this->imageFactory->create($originalImagePath);
        $image->keepAspectRatio($imageParams['keep_aspect_ratio']);
        $image->keepFrame($imageParams['keep_frame']);
        $image->keepTransparency($imageParams['keep_transparency']);
        $image->constrainOnly($imageParams['constrain_only']);
        $image->backgroundColor($imageParams['background']);
        $image->quality($imageParams['quality']);
        return $image;
    }

}
