<?php
/**
* 
*/

namespace WPPFW\Database\Wordpress;

/**
* 
*/
class MUWordpressOptions 
{
	
	/**
	* put your comment there...
	* 
	* @var mixed
	*/
	protected $blogId;
	
	/**
	* put your comment there...
	* 
	* @var mixed
	*/
	protected $prefix;

	/**
	* put your comment there...
	* 
	* @param mixed $prefix
	* @param mixed $blogId
	* @return WordpressOptions
	*/
	public function __construct( $prefix, $blogId = null ) 
	{
		$this->blogId = $blogId;
		$this->prefix =& $prefix;
	}
	
	/**
	* put your comment there...
	* 
	* @param WPOptionVariable $variable
	* @return {WPOptionVariable|WPOptionVariable}
	*/
	public function get( WPOptionVariable & $variable ) 
	{

		$variable->setValue( get_blog_option( $this->getBlogId(), $this->getOptionFullName( $variable ), $variable->getValue() ) );
		
		return $variable;
	}

	/**
	* put your comment there...
	* 
	*/
	public function getBlogId()
	{
		return $this->blogId;
	}
	
	/**
	* put your comment there...
	* 
	* @param WPOptionVariable $name
	* @return {mixed|WPOptionVariable}
	*/
	public function getOptionFullName( WPOptionVariable & $variable ) 
	{
		return "{$this->getPrefix()}{$variable}";
	}
	
	/**
	* put your comment there...
	* 
	*/
	public function getPrefix() 
	{
		return $this->prefix;
	}

	/**
	* put your comment there...
	* 
	* @param WPOptionVariable $varilable
	* @param mixed $value
	* @return WordpressOptions
	*/
	public function & set( WPOptionVariable & $variable ) 
	{
		
		update_blog_option( $this->getBlogId(),  $this->getOptionFullName( $variable ), $variable->getValue() );
		
		return $variable;
	}

}
