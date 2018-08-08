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
        // Return empty Response if not a POST request
        if (! $request->isMethod('POST')) {
            
            return $this->generateResponse('Not a POST method.', Response::HTTP_METHOD_NOT_ALLOWED);
            
        }
        
        if (! $request->files->get('file')) {
            
            return $this->generateResponse('No uploaded file.', Response::HTTP_BAD_REQUEST);

        }
        
        if (! $request->request->has('upload')) {
            
            return $this->generateResponse('No upload parameters.', Response::HTTP_BAD_REQUEST);
            
        }
        
        $upload = $request->request->get('upload');
        $formats = $request->request->get('formats');
        
        if (! in_array($upload, ['dropbox', 's3', 'ftp'])) {

            return $this->generateResponse('Unkown upload.', Response::HTTP_BAD_REQUEST);

        }
        
        if (! empty($formats) && ! in_array($formats, ['mp4', 'webm', 'ogv'])) {

            return $this->generateResponse('Unkown format.', Response::HTTP_BAD_REQUEST);

        }
        
        $file = $request->files->get('file');

        $returnData = [];
        $returnData['url'] = $this->manageUpload($upload, $file);

        return $this->generateResponse('OK.', Response::HTTP_OK, $returnData);
        
        return $response;
    }
    
    /**
     * Upload method
     *
     * @param string $upload 
     * @param SplFileInfo $file 
     * @return string
     * @todo check return type, convert to UploadManager
     */
    protected function manageUpload(string $upload, $file)
    {
        switch ($upload) {
            case 'dropbox':
                $client = new DropboxClient($this->config['dropbox']['access_key'], $this->config['dropbox']['secret_token'], $this->config['dropbox']['container']);                                       
                return $client->upload($file);
                
            case 's3':
                $client = new S3Client($this->config['s3']['access_key_id'], $this->config['s3']['secret_access_key']);                                       
                $response = $client->send($file, $this->config['s3']['bucketname']);  

                return $response->getPublicUrl();
                
            case 'ftp':
                $client = new FTPUploader();                                       
                $response = $client->uploadFile(
                    $file, 
                    $this->config['ftp']['hostname'], 
                    $this->config['ftp']['username'],
                    $this->config['ftp']['password'],
                    $this->config['ftp']['destination']                                        
                );   
                
                // @todo add if response ...
                
                return sprintf('ftp://%s/%s/%s', $this->config['ftp']['hostname'], $this->config['ftp']['destination'], $file->getClientOriginalName());            
            
            default:
                # THROW ERROR
        }
    }
    
    /**
     * Generate Response
     *
     * @param string $message 
     * @param int $code 
     * @param array $content 
     * @return Response
     */
    protected function generateResponse(string $message, int $code, array $content = [])
    {
        $response =  new Response(json_encode($content));
        $response->setStatusCode($code);
        $response->setCharset('UTF-8');
        $response->headers->set('Content-Type', 'application/json');   
             
        return $response;
    }
}
