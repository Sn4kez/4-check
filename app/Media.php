<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Ramsey\Uuid\Uuid;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class Media extends Model {

    public static function processRequest($data) {
        if (array_key_exists('source_b64', $data)) {
            $mediaName = self::uploadImageBase64($data['source_b64']);

            if (!is_null($mediaName)) {
                $data['image'] = $mediaName;
            }
        }

        return $data;
    }

    /**
     * @param $contentBase64
     * @return null|string
     */
    public static function uploadImageBase64($contentBase64) {
        if(is_string($contentBase64)) {
            $replace = [
                "data:image/jpeg;base64,",
                "data:image/png;base64,",
                "data:image/gif;base64,",
                "data:image/jpg;base64,"
            ];

            $contentBase64 = str_replace($replace, [
                "",
                "",
                "",
                ""
            ], $contentBase64);

            $extension = self::getImageTypeFromBase64String($contentBase64);
            $name = self::getImageName($extension);
            $path = self::getPath($name);
            $content = base64_decode($contentBase64);

            if (!is_null($content) && strlen($content) > 0) {
                file_put_contents($path, $content);

                return $name;
            }
        }

        return null;
    }

    /**
     * @param $base64String
     * @return mixed
     */
    public static function getImageTypeFromBase64String($base64String) {
        if(is_string($base64String)) {
            $imgData = base64_decode($base64String);
            $finfo = finfo_open();
            $mimeType = finfo_buffer($finfo, $imgData, FILEINFO_MIME_TYPE); // e.g. image/png
            $parts = explode("/", $mimeType); // [0 => image, 1 => png]

            return count($parts) > 1 ? $parts[1] : $parts[0];
        }

        return null;
    }

    private static function getImageName($extension) {
        return sprintf("%s.%s", Uuid::uuid4()->toString(), $extension);
    }

    /**
     * Returns the path for a media id
     *
     * @param $mediaName
     * @return string
     */
    public static function getPath($mediaName) {
        $path = sprintf("%s/media/%s/%s/%s/%s/%s", base_path(), $mediaName[0], $mediaName[1], $mediaName[2], $mediaName[3], $mediaName);
        @mkdir(dirname($path), 0777, true);

        return $path;
    }

    public static function doesFileExist($mediaName) {
        return file_exists(self::getPath(basename($mediaName)));
    }

    public static function getFileExtension($path) {
        return pathinfo($path, PATHINFO_EXTENSION);
    }

    public static function isImage($path) {
        if (file_exists($path)) {
            return getimagesize($path) !== false;
        } else {
            return false;
        }
    }

    public static function getHeaderForImages($path) {
        $extension = strtolower(self::getFileExtension($path));

        switch ($extension) {
            case "png":
                return "image/png";
            case "gif":
                return "image/gif";
            case "jpg":
            case "jpeg":
                return "image/jpeg";
            default:
                return null;
        }
    }

    public static function getShortHeaderForImages($path) {
        return str_replace('image/', '', self::getHeaderForImages($path));
    }

    public static function getBase64String($path) {
        try {
            $fileContents = file_get_contents($path);
        } catch (\ErrorException $e) {
            $fileContents = false;
        }
        if ($fileContents === false) {
            return 'data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP';
        }

        return sprintf("data:image/%s;base64,%s", self::getShortHeaderForImages($path), base64_encode($fileContents));
    }

    public static function changeImageFilenameToBase64String(Model $model) {
        if (!is_null($model->image)) {
            $currentImage = $model->image;

            if (!is_null($currentImage) && strlen($currentImage) > 0) {
                $path = Media::getPath($currentImage);

                if (file_exists($path)) {
                    /**
                     * It is not a base64 content at the object, it is a filename.
                     * So now we need to convert the file to base64
                     */
                    $model->image = Media::getBase64String($path);
                }
            }
        }

        return $model;
    }

    public static function uploadUnitTestTestImage($path) {
        $base64ContentForUpload = base64_encode(file_get_contents($path));
        $mediaName = Media::uploadImageBase64($base64ContentForUpload);
        $mediaBase64ContentCheckString = Media::getBase64String($path);

        return [
            "mediaName" => $mediaName,
            "base64Content" => $mediaBase64ContentCheckString
        ];
    }
}
