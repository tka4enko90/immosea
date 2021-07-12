<?php
/**
* 
*/

namespace WPPFW\Forms\Fields;

# Important
use WPPFW\Forms\Types\TypeString;

/**
* 
*/
class FormStringField extends FormField {
	
	/**
	* put your comment there...
	* 
	*/
	protected function getType() {
		return new TypeString();
	}

}