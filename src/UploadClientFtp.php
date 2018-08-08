<?php

namespace Blossom\BackendDeveloperTest;

use FTPStub\FTPUploader;

/**
 * UploadClientDropbox
 */
class UploadClientFtp extends AbstractUploadClient
{
    /**
     * {@inheritdoc}
     */
    public function upload() : string
    {
        $client = new FTPUploader();                                       
        $response = $client->uploadFile(
            $this->file, 
            $this->config['hostname'], 
            $this->config['username'],
            $this->config['password'],
            $this->config['destination']                                        
        );   
        
        return $response ? sprintf('ftp://%s/%s/%s', $this->config['hostname'], $this->config['destination'], $this->file->getClientOriginalName()) : '';            
    }
}