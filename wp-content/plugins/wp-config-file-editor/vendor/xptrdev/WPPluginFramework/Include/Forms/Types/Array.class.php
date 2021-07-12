<?php
/**
* 
*/

namespace WPPFW\Forms\Types;

/**
* 
*/
class TypeArray extends TypeBase {
	
	/**
	* put your comment there...
	* 
	* @param mixed $value
	*/
	protected function typeCast( $value ) {
		# Cast
		return ( $value ? $value : array() );
	}
	
}
