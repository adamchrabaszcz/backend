<?php

namespace Blossom\BackendDeveloperTest;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use DropboxStub\DropboxClient;
use FTPStub\FTPUploader;
use S3Stub\Client as S3Client;
use S3Stub\FileObject;
use FFMPEGStub\FFMPEG;
use EncodingStub\Client as EncodingClient;

/**
 * You should implement this class however you want.
 * 
 * The only requirement is existence of public function `handleRequest()`
 * as this is what is tested. The constructor's signature must not be changed.
 */
class Application implements ApplicationInterface
{
    /**
     * Configuration Array
     *
     * @var array
     */
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
        // Check if Request is invalid and return if so
        if ($invalidRequestResponse = $this->isInvalidRequest($request)) {
            return $invalidRequestResponse;
        }
        
        // Get upload and formats parameters from POST
        $upload = $request->request->get('upload');
        $formats = $request->request->get('formats');
        
        // Get file
        $file = $request->files->get('file');

        $returnData = [];
        
        // Upload file
        $returnData['url'] = $this->manageUpload($upload, $file);
        
        // Convert files and upload (if needed)
        $returnData['formats'] = $this->convertFilesAndUpload($file, $formats, $upload); 

        // Return Response
        return $this->generateResponse('OK.', Response::HTTP_OK, $returnData);
    }
    
    /**
     * Check if Request is invalid
     *
     * @param Request $request 
     * @return Response | null
     */
    protected function isInvalidRequest(Request $request) : ?Response
    {
        // Return HTTP_METHOD_NOT_ALLOWED if not a POST request
        if (! $request->isMethod('POST')) {
            
            return $this->generateResponse('Not a POST method.', Response::HTTP_METHOD_NOT_ALLOWED);
            
        }
        
        // Return HTTP_BAD_REQUEST if no file sent
        if (! $request->files->get('file')) {
            
            return $this->generateResponse('No uploaded file.', Response::HTTP_BAD_REQUEST);

        }
        
        // Return HTTP_BAD_REQUEST if no upload parameters
        if (! $request->request->has('upload')) {
            
            return $this->generateResponse('No upload parameters.', Response::HTTP_BAD_REQUEST);
            
        }
        
        // Check if upload is proper type
        if (! in_array($request->request->get('upload'), ['dropbox', 's3', 'ftp'])) {

            return $this->generateResponse('Unkown upload.', Response::HTTP_BAD_REQUEST);

        }
        
        // Check if formats are proper types
        if (! empty($request->request->get('formats')) && count(array_intersect($request->request->get('formats'), ['mp4', 'webm', 'ogv'])) === 0) {

            return $this->generateResponse('Unkown format.', Response::HTTP_BAD_REQUEST);

        }
        
        return null;
    }
    
    /**
     * Convert Files And Upload (possibly)
     *
     * @param \SplFileInfo $file 
     * @param array $toFormats 
     * @param string $upload 
     * @return array
     */
    protected function convertFilesAndUpload(\SplFileInfo $file, array $toFormats, string $upload) : array
    {
        // Return empty array if no formats to convert to
        if (empty($toFormats)) {
            
            return [];
        }
        
        $encodedFiles = [];
        
        // Foreach format encode and add to array
        foreach ($toFormats as $format) {
            // If extension different than mp4, then Encode
            if ($file->getExtension() !== $format) {
                $client = new EncodingClient(
                    $this->config['encoding.com']['app_id'],
                    $this->config['encoding.com']['access_token']                    
                );
                $encodedFiles[$format] = $client->encodeFile($file, $format);
            // Else Convert and upload
            } else {
                $client = new FFMPEG();
                $convertedFile = $client->convert($file);
                $encodedFiles[$format] = $this->manageUpload($upload, $convertedFile);
            }            
        }
        
        return $encodedFiles;
    }
    
    /**
     * Upload method
     *
     * @param string $upload 
     * @param \SplFileInfo $file 
     * @return string
     */
    protected function manageUpload(string $upload, \SplFileInfo $file) : string
    {
        $client = UploadClientFactory::createFromConfigAndUploadAndFile($this->config, $upload, $file);
        
        return $client->upload();
        
    }
    
    /**
     * Generate Response
     *
     * @param string $message 
     * @param int $code 
     * @param array $content 
     * @return Response
     */
    protected function generateResponse(string $message, int $code, array $content = []) : Response
    {
        $response =  new Response(json_encode($content));
        $response->setStatusCode($code);
        $response->setCharset('UTF-8');
        $response->headers->set('Content-Type', 'application/json');   
             
        return $response;
    }
}
