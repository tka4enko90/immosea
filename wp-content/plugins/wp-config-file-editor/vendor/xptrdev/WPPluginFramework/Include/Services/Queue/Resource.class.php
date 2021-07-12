<?php
/**
* 
*/

namespace WPPFW\Services\Queue;

/**
* 
*/
class Resource 
{
	
	/**
	* put your comment there...
	* 
	* @var mixed
	*/
	protected $dependencies;

    /**
    * put your comment there...
    * 
    * @var mixed
    */
    protected $filePath;
    	
	/**
	* put your comment there...
	* 
	* @var mixed
	*/
	protected $fileName;
	
	/**
	* put your comment there...
	* 
	* @var mixed
	*/
	protected $name;
	
    /**
    * put your comment there...
    * 
    * @var mixed
    */
    protected $path;
    
	/**
	* put your comment there...
	* 
	* @var mixed
	*/
	protected $url;
	
	/**
	* put your comment there...
	* 
	* @var mixed
	*/
	protected $version;
	
    /**
    * put your comment there...
    * 
    * @param mixed $name
    * @param mixed $url
    * @param mixed $path
    * @param mixed $version
    * @return Resource
    */
	public function __construct($name, $url, $path, $version = null) 
    {
		# Initialize
		$this->name =& $name;
		$this->url = "{$url}/{$this->fileName}";
        $this->path = $path;
        $this->filePath = $path . DIRECTORY_SEPARATOR . $this->fileName;
		$this->version =& $version;
		$this->dependencies = new \WPPFW\Collection\DataAccess();
	}

	/**
	* put your comment there...
	* 
	* @return \WPPFW\Collection\DataAccess
	*/
	public function & dependencies() 
    {
		return $this->dependencies;
	}

    /**
    * put your comment there...
    * 
    */
    public function getFileName()
    {
        return $this->fileName;
    }
    
    /**
    * put your comment there...
    * 
    */
    public function getFilePath()
    {
        return $this->filePath;
    }
    
    /**
    * put your comment there...
    * 
    */
    public function getPath()
    {
        return $this->path;
    }
    
	/**
	* put your comment there...
	* 
	*/
	public function getName() 
    {
		return $this->name;
	}

	/**
	* put your comment there...
	* 
	*/
	public function getUrl() 
    {
		return $this->url;
	}
	
	/**
	* put your comment there...
	* 
	*/
	public function getVersion() 
    {
		return $this->version;
	}

}
	