<?php

namespace Blossom\BackendDeveloperTest;

/**
 * Abstract UploadClient
 */
abstract class AbstractUploadClient implements UploadClientInterface
{
    /**
     * config.
     *
     * @var array $config
     */
    protected $config;
    
    /**
     * file.
     *
     * @var \SplFileInfo $file
     */
    protected $file;
    
    /**
     * Set File.
     *
     * @param \SplFileInfo $file
     *
     * @return this
     */
    public function setFile(\SplFileInfo $file)
    {
      $this->file = $file;
      
      return $this;
    }
    
    /**
     * Get File.
     *
     * @return \SplFileInfo
     */
    public function getFile() : \SplFileInfo
    {
      return $this->file;
    }
    
    /**
     * Set Config.
     *
     * @param array $config
     *
     * @return this
     */
    public function setConfig(array $config)
    {
      $this->config = $config;
      
      return $this;
    }
    
    /**
     * Get Config.
     *
     * @return array
     */
    public function getConfig() : array
    {
      return $this->config;
    }
    
    /**
     * Abstract upload method
     *
     * @return string
     */
    abstract public function upload() : string;
}