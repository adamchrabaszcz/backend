<?php

namespace Blossom\BackendDeveloperTest;

use S3Stub\Client as S3Client;

/**
 * UploadClientDropbox
 */
class UploadClientS3 extends AbstractUploadClient
{
    /**
     * {@inheritdoc}
     */
    public function upload() : string
    {
        $client = new S3Client(
                        $this->config['access_key_id'], 
                        $this->config['secret_access_key']
                    );      
                                                     
        $response = $client->send($this->file, $this->config['bucketname']);  

        return $response->getPublicUrl();
    }
}