<?php

namespace App\Repositories;


use App\Enums\CounterType;
use App\Models\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Sajya\Server\Exceptions\InvalidParams;

class FileRepository
{
    private string $uploadSubDir = '/';

    /**
     * @param string $originalFileName
     * @param string $baseData
     * @return string|File
     */
    public function uploadFileToStorage(string $originalFileName, string $baseData = null): string|File
    {
        $fileType = $this->getFileTypeFromFileName($originalFileName);
        $encodedFileName = $this->encodeFileName($originalFileName) . '.' . $fileType;
        $storagePath = $this->getUploadSubDir() . $encodedFileName;
        $data = explode(',', $baseData);
        if (str_contains($data[0], 'base64')) {
            if ($this->uploadBase64ToStorage($baseData, $storagePath)) {
                $fileParams['storage_path'] = $storagePath;
                $fileParams['origin_file_name'] = $originalFileName;
                $fileParams['encoded_file_name'] = $encodedFileName;
                try {
                    return $this->setFileObjData($fileParams);
                } catch (\Exception $e) {
                    return $e->getMessage();
                }
            }
        } else {
            throw new InvalidParams(['message' => 'need base64 string to save file']);
        }
        throw new InvalidParams(['message' => 'invalid encoding']);
    }


    /**
     * @param array $fileParams
     * @return File
     */
    public function setFileObjData(array $fileParams): File
    {
        $saveFilePath = $fileParams['storage_path'];
        $fileName = $fileParams['origin_file_name'];
        $encodedFileName = $fileParams['encoded_file_name'];

        $fileObj = new File();
        $savedFileUrl = Storage::url($saveFilePath);
        $mimeType = Storage::mimeType($saveFilePath);
        $fileSize = Storage::size($saveFilePath);
        $fileObj->original_name = $fileName;
        $fileObj->encoded_name = $encodedFileName;
        $fileObj->path = $savedFileUrl;
        $fileObj->mime_type = $mimeType;
        $fileObj->size = $fileSize;

        return $fileObj;
    }

    /**
     * @param string $fileName
     * @return string
     */
    public function getFileTypeFromFileName(string $fileName): string
    {
        preg_match('/[a-z]+$/', $fileName, $matches);
        return $matches[0];
    }

    /**
     * @param string $fileName
     * @return string
     */
    public function encodeFileName(string $fileName): string
    {
        return md5(Str::uuid()->toString() . md5($fileName));
    }


    /**
     * @param string $base64_string
     * @param string $storagePath
     * @return bool
     */
    private function uploadBase64ToStorage(string $base64_string, string $storagePath): bool
    {
        $data = explode(',', $base64_string);
        return Storage::disk('local')->put($storagePath, base64_decode($data[1]));
    }

    /**
     * @return string
     */
    public function getUploadSubDir(): string
    {
        return $this->uploadSubDir;
    }

    /**
     * @param string $uploadSubDir
     */
    public function setUploadSubDir(string $uploadSubDir): void
    {
        $this->uploadSubDir = $uploadSubDir;
    }
}
