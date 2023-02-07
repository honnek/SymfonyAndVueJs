<?php


namespace App\Utils\Manager;

use App\Entity\ProductImage;
use App\Utils\File\ImageResizer;
use App\Utils\Filesystem\FilesystemWorker;
use Doctrine\ORM\EntityManagerInterface;

class ProductImageManager
{

    private EntityManagerInterface $entityManager;
    private FilesystemWorker $filesystemWorker;
    private string $uploadsTempDir;
    private ImageResizer $imageResizer;

    /**
     * @param EntityManagerInterface $entityManager
     * @param FilesystemWorker $filesystemWorker
     * @param ImageResizer $imageResizer
     * @param string $uploadsTempDir
     */
    public function __construct(
        EntityManagerInterface $entityManager,
        FilesystemWorker       $filesystemWorker,
        ImageResizer           $imageResizer,
        string                 $uploadsTempDir,
    )
    {
        $this->entityManager = $entityManager;
        $this->filesystemWorker = $filesystemWorker;
        $this->uploadsTempDir = $uploadsTempDir;
        $this->imageResizer = $imageResizer;
    }

    /**
     * @param string $productDir
     * @param string|null $tempImageFilename
     * @return ProductImage|null
     */
    public function saveImageForProduct(string $productDir, string $tempImageFilename = null): ?ProductImage
    {
        if (!$tempImageFilename) {
            return null;
        }

        $this->filesystemWorker->createFolder($productDir);

        $imageSmallParams = [
            'width' => 60,
            'height' => null,
            'newFolder' => $productDir,
            'newFileName' => sprintf('%s_%s.jpg', uniqid(), 'small'),
        ];
        $imageSmall = $this->imageResizer->resizeImageAndSave(
            $this->uploadsTempDir,
            $tempImageFilename,
            $imageSmallParams,
        );

        $imageMiddleParams = [
            'width' => 430,
            'height' => null,
            'newFolder' => $productDir,
            'newFileName' => sprintf('%s_%s.jpg', uniqid(), 'middle'),
        ];
        $imageMiddle = $this->imageResizer->resizeImageAndSave(
            $this->uploadsTempDir,
            $tempImageFilename,
            $imageMiddleParams,
        );

        $imageBigParams = [
            'width' => 800,
            'height' => null,
            'newFolder' => $productDir,
            'newFileName' => sprintf('%s_%s.jpg', uniqid(), 'big'),
        ];
        $imageBig = $this->imageResizer->resizeImageAndSave(
            $this->uploadsTempDir,
            $tempImageFilename,
            $imageBigParams,
        );

        $productImage = new ProductImage();
        $productImage->setFilenameSmall($imageSmall);
        $productImage->setFilenameMiddle($imageMiddle);
        $productImage->setFilenameBig($imageBig);

        return $productImage;
    }

}