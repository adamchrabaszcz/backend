<?php
namespace Blossom\BackendDeveloperTest;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use DropboxStub\DropboxClient;
use FTPStub\FTPUploader;
use S3Stub\Client as S3Client;
use S3Stub\FileObject;

/**
 * You should implement this class however you want.
 * 
 * The only requirement is existence of public function `handleRequest()`
 * as this is what is tested. The constructor's signature must not be changed.
 */
class Application
{
    protected $config;
    
    
    /**
     * By default the constructor takes a single argument which is a config array.
     *
     * You can handle it however you want.
     * 
     * @param array $config Application config.
     */
    public function __construct(array $config)
    {
        $this->config = $config;
    }

    /**
     * This method should handle a Request that comes pre-filled with various data.
     *
     * You should implement it however you want and it should return a Response
     * that passes all tests found in EncoderTest.
     * 
     * @param  Request $request The request.
     *
     * @return Response
     */
    public function handleRequest(Request $request): Response
    {
        $upload = $request->request->get('upload');
        $format = $request->request->get('format');
        $file = $request->files->get('file');
        
        $uploadResponse = '';
        
        switch ($upload) {
            case 'dropbox':
                $client = new DropboxClient($this->config['dropbox']['access_key'], $this->config['dropbox']['secret_token'], $this->config['dropbox']['container']);                                       
                $uploadResponse = $client->upload($file);                 
                break;
                
            case 's3':
                $client = new S3Client($this->config['s3']['access_key_id'], $this->config['s3']['secret_access_key']);                                       
                $uploadResponse = $client->send($file, $this->config['s3']['bucketname']);                 
                break;    
                
            case 'ftp':
                $client = new FTPUploader();                                       
                $uploadResponse = $client->uploadFile(
                    $file, 
                    $this->config['ftp']['hostname'], 
                    $this->config['ftp']['username'],
                    $this->config['ftp']['password'],
                    $this->config['ftp']['destination']                                        
                );                 
                break;                                
            
            default:
                # THROW ERROR
                break;
        }
        
        echo '<pre>';
        print_r($uploadResponse);
        echo '</pre>';



        $response = new Response();
        $response->setCharset('UTF-8');
        
        return $response;
    }
}
