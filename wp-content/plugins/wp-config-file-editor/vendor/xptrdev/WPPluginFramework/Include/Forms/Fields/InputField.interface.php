<?php
/**
* 
*/

namespace WPPFW\Forms\Fields;

# Field rule type
use WPPFW\Forms\Rules\IFieldRule;

/**
* 
*/
interface IInputField {
	
	/**
	* 
	*/
	public function getRules();
	
	/**
	* 
	*/
	public function hasRules();
	
	/**
	* 
	*/
	public function & setRule(IFieldRule $rule);

}
