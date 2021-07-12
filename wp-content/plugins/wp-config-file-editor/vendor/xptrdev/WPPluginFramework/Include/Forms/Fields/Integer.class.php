<?php
/**
* 
*/

namespace WPPFW\Forms\Fields;

# Important
use WPPFW\Forms\Types\TypeInteger;

/**
* 
*/
class FormIntegerField extends FormField {
	
	/**
	* put your comment there...
	* 
	*/
	protected function getType() {
		return new TypeInteger();
	}

}