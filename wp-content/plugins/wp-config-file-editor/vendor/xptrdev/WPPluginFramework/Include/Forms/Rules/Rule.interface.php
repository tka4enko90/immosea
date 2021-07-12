<?php
/**
* 
*/

namespace WPPFW\Forms\Rules;

# Form Field
use WPPFW\Forms\Fields\FormField;

/**
* 
*/
interface IFieldRule {
	
	/**
	* 
	*/
	public function & bind(FormField & $field);
	
	/**
	* 
	*/
	public function getErrorMessage();
	
	/**
	* 
	*/
	public function isError();
	
	/**
	* 
	*/
	public function isValid();
	
}