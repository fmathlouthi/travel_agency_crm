<?php

/**
 * This file is part of the pdAdmin package.
 *
 * @package     pd-admin
 * @license     LICENSE
 * @author      Ramazan APAYDIN <apaydin541@gmail.com>
 * @link        https://github.com/appaydin/pd-admin
 */

namespace App\Manager;

use App\Library\Tools;
use Intervention\Image\Image;
use Intervention\Image\ImageManager;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * Upload File Manager.
 *
 * @author Ramazan APAYDIN <apaydin541@gmail.com>
 */
class UploadManager
{
    /**
     * @var ParameterBagInterface
     */
    private $parameterBag;

    /**
     * Current Upload Directory.
     *
     * @var string
     */
    private $currentDir;

    /**
     * Upload Relative Path.
     *
     * @var string
     */
    private $currentPath;

    /**
     * Upload constructor.
     */
    public function __construct(ParameterBagInterface $parameterBag)
    {
        $this->parameterBag = $parameterBag;
        $this->createDirectory();
    }

    /**
     * Upload Files & Encode ImageManager.
     *
     * @param $files array|UploadedFile
     * @param bool $rawUpload
     */
    public function upload($files, $rawUpload = false): array
    {
        // Uploaded Files
        $uploadFiles = [];

        // Convert Array
        if ($files instanceof UploadedFile) {
            $files = [$files];
        }

        // Start Upload
        if (\is_array($files)) {
            foreach ($files as $file) {
                if ($file instanceof UploadedFile) {
                    $uploadFiles[] = $this->uploadProcess($file, $rawUpload);
                }
            }
        }

        // Return Uploaded Files
        return $uploadFiles;
    }

    /**
     * Remove Files.
     *
     * @param string|array|null $files
     */
    public function removeFiles($files)
    {
        if ($files) {
            // Convert Array
            if (!\is_array($files)) {
                $files = [$files];
            }

            foreach ($files as $file) {
                $file = $this->cfg('upload_dir').$file;
                if (file_exists($file)) {
                    unlink($file);
                }
            }
        }
    }

    /**
     * Start Upload.
     *
     * @param $rawUpload boolean
     */
    private function uploadProcess(UploadedFile $file, $rawUpload): string
    {
        // Create Filename
        $tools = new Tools();
        $fileName = $tools::webalize($tools::randomStr(6).$file->getClientOriginalName(), '.');

        // Upload File and Optimize Images
        if (!$rawUpload) {
            switch ($file->getClientMimeType()) {
                case image_type_to_mime_type(IMAGETYPE_JPEG):
                case image_type_to_mime_type(IMAGETYPE_PNG):
                    $this->imageManager($file, $this->currentPath.'/'.$fileName);
                    break;
                default:
                    $file->move($this->currentPath, $fileName);
            }
        } else {
            $file->move($this->currentPath, $fileName);
        }

        // Return Filename
        return $this->currentDir.'/'.$fileName;
    }

    /**
     * Image Process Manager.
     *
     * @param $filePath string
     */
    private function imageManager(UploadedFile $file, $filePath): void
    {
        // Create Image Manager
        $img = new ImageManager(['driver' => $this->cfg('media_library')]);
        $img = $img->make($file->getRealPath());

        // Image Optimize
        if ($this->cfg('media_optimize')) {
            $img->resize($this->cfg('media_max_height'), $this->cfg('media_max_width'), function ($constraint) {
                $constraint->upsize();
                $constraint->aspectRatio();
            });
        }

        // Image Add Watermark
        switch ($this->cfg('media_watermark')) {
            case 'text':
                $this->addTextWatermark($img);
                break;
            case 'image':
                $this->addImageWatermark($img);
                break;
        }

        // Save Image
        $img->save($filePath, $this->cfg('media_optimize') ? $this->cfg('media_quality') : null)->destroy();
    }

    /**
     * Image Add Text Watermark.
     *
     * @param Image $img
     */
    private function addTextWatermark(&$img): void
    {
        // Set X-Y Image Ordinate
        $xOrdinate = $img->getWidth() * $this->cfg('media_wm_font_x');
        $yOrdinate = $img->getHeight() * $this->cfg('media_wm_font_y');

        // Add Text Watermark
        $img->text($this->cfg('media_wm_font_text'), $xOrdinate, $yOrdinate, function ($font) {
            // Exist Font File
            if (!empty($this->cfg('media_wm_font')) && file_exists($fontPath = $this->cfg('upload_dir').$this->cfg('media_wm_font'))) {
                $font->file($fontPath);
            }

            $font->size($this->cfg('media_wm_font_size'));
            $font->color($this->cfg('media_wm_font_color'));
            $font->align($this->cfg('media_wm_font_align'));
            $font->valign($this->cfg('media_wm_font_valign'));
            $font->angle($this->cfg('media_wm_font_angle'));
        });
    }

    /**
     * Image Add Image Watermark.
     *
     * @param Image $img
     */
    private function addImageWatermark(&$img): void
    {
        if (file_exists($imagePath = $this->cfg('upload_dir').$this->cfg('media_wm_image'))) {
            $img->insert(
                $imagePath,
                $this->cfg('media_wm_image_position'),
                $this->cfg('media_wm_image_x'),
                $this->cfg('media_wm_image_y')
            );
        }
    }

    /**
     * Create Upload Directory.
     */
    private function createDirectory(): void
    {
        // Create Current Directory
        $this->currentDir = date('Y/m/d');
        $this->currentPath = $this->cfg('upload_dir').$this->currentDir;

        // Create Directory
        if (!file_exists($this->currentPath)) {
            // Create Filesystem
            $fs = new Filesystem();

            // Create Dir
            $fs->mkdir($this->currentPath);
        }
    }

    /**
     * Get Parameter.
     *
     * @param $parameterName string
     *
     * @return mixed
     */
    private function cfg($parameterName)
    {
        return $this->parameterBag->get($parameterName);
    }
}
