<?php
/**
* 
*/

namespace WPPFW\Forms\Fields;

# Important
use WPPFW\Forms\Types\TypeRaw;

/**
* 
*/
class FormRawField extends FormField {
	
	/**
	* put your comment there...
	* 
	*/
	protected function getType() {
		return new TypeRaw();
	}

}