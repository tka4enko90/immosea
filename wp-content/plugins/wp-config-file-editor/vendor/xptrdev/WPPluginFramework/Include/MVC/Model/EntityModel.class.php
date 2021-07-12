<?php
/**
* 
*/

namespace WPPFW\MVC\Model;
/**
* 
*/
class EntityModel
{
	
	/**
	* put your comment there...
	* 
	* @param mixed $data
	* @return EntityModel
	*/
	public function __construct( $data = array() )
	{
		$this->exchangeArray( $data );
	}
	
	/**
	* put your comment there...
	* 
	* @param mixed $data
	* @return EntityModel
	*/
	public function & exchangeArray( $data )
	{
		
		foreach ( get_object_vars( $this ) as $name => $value )
		{
			$this->$name = isset( $data[ $name ] ) ? $data[ $name ] : null;
		}
		
		return $this;
	}
	
	/**
	* put your comment there...
	* 
	*/
	public function getArray()
	{
		
		$data = array();
		
		foreach ( get_object_vars( $this ) as $name => $value )
		{
			if ( $value !== null )
			{
				$data[ $name ] = $value;
			}
		}
		
		return $data;
	}

}