<?php

namespace App\Repositories;


use App\Enums\CounterType;
use App\Models\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class FileRepository
{
    /**
     * @param string $subDirName
     * @param $originalFileName
     * @param $baseData
     * @return string|File
     */
    public function uploadFileToStorage(string $subDirName, $originalFileName, $baseData = null): string|File
    {
        $fileType = $this->getFileTypeFromFileName($originalFileName);
        $encodedFileName = $this->encodeFileName($originalFileName) . '.' . $fileType;
        $storagePath =  $subDirName . $encodedFileName;
        if (is_string($baseData)) {
            $data = explode(',', $baseData);
            if (str_contains($data[0], 'base64')) {
                if ($this->uploadBase64ToStroage($baseData, $storagePath)) {
                    $fileParams['storage_path'] = $storagePath;
                    $fileParams['origin_file_name'] = $originalFileName;
                    $fileParams['encoded_file_name'] = $encodedFileName;
                    try {
                        return $this->setFileObjData($fileParams);
                    }catch (\Exception $e){
                        return $e->getMessage();
                    }
                }
            }
        }

        return 'Unknown file encoding';
    }


    /**
     * @param array $fileParams
     * @return File
     */
    public function setFileObjData(array $fileParams): File
    {
        $saveFilePath = $fileParams['storage_path'] ;
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
     * @param $fileName
     * @return string
     */
    public function getFileTypeFromFileName($fileName): string
    {
        preg_match('/[a-z]+$/', $fileName, $matches);
        return $matches[0];
    }

    /**
     * @param $fileName
     * @return string
     */
    public function encodeFileName($fileName): string
    {
        return md5(Str::uuid()->toString() . md5($fileName));
    }


    /**
     * @param string $base64_string
     * @param string $storagePath
     * @return bool
     */
    private function uploadBase64ToStroage(string $base64_string, string $storagePath): bool
    {
        $data = explode(',', $base64_string);
        return Storage::disk('local')->put($storagePath, base64_decode($data[1]));
    }
}
