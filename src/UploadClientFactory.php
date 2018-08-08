<?php

namespace Blossom\BackendDeveloperTest;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * UploadClient Factory
 *
 */
class UploadClientFactory
{
    /**
     * Create UploadClient from config, upload and file
     *
     * @param array $config 
     * @param string $upload 
     * @param SplFileInfo $file 
     * @return UploadClientInterface
     */
    public function createFromConfigAndUploadAndFile(array $config, string $upload, \SplFileInfo $file) : UploadClientInterface
    {
        $uploadClientName = sprintf('Blossom\BackendDeveloperTest\UploadClient%s', ucwords($upload));
        $uploadClient = new $uploadClientName();
        $uploadClient->setConfig($config[$upload]);
        $uploadClient->setFile($file);
        
        return $uploadClient;

    }
    
}