<?php

namespace Blossom\BackendDeveloperTest;

use DropboxStub\DropboxClient;

/**
 * UploadClientDropbox
 */
class UploadClientDropbox extends AbstractUploadClient
{
    /**
     * {@inheritdoc}
     */
    public function upload() : string
    {
        $client = new DropboxClient(
                    $this->config['access_key'], 
                    $this->config['secret_token'], 
                    $this->config['container']
                );   
                                                    
        return $client->upload($this->file);
    }
}