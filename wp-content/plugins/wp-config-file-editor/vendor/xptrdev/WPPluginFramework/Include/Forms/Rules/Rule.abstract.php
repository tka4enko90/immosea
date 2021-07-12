<?php
/**
* 
*/

# Define namespace
namespace WPPFW\Forms\Rules;

# Form field
use WPPFW\Forms\Fields\FormField;

/**
* 
*/
abstract class FieldRule implements IFieldRule {
	
	/**
	* put your comment there...
	* 
	* @var mixed
	*/
	protected $errorMessage;
	
	/**
	* put your comment there...
	* 
	* @var mixed
	*/
	protected $field;
	
	/**
	* put your comment there...
	* 
	* @var mixed
	*/
	protected $validated;
	
	/**
	* put your comment there...
	* 
	* @param FormField $field
	* @return FormField
	*/
	public function & bind(FormField & $field) {
		# Associate to field
		$this->field =& $field;
		# Chain
		return $this;
	}

	/**
	* put your comment there...
	* 
	*/
	public function getErrorMessage() {
		return $this->errorMessage;
	}

	/**
	* put your comment there...
	* 
	*/
	public function & getField() {
		return $this->field;
	}

    /**
    * put your comment there...
    * 
    * @param mixed $message
    */
    protected abstract function getMessageString( $message );
    
	/**
	* put your comment there...
	* 
	*/
	protected function getValue() {
		return $this->getField()->getValue();
	}
	
	/**
	* put your comment there...
	* 
	*/
	public function isError() {
		return !$this->validated;
	}

	/**
	* put your comment there...
	* 
	*/
	public function isValid() {
		# Validate
		return $this->validated = $this->validate();
	}
	
	/**
	* put your comment there...
	* 
	* @param mixed $message
	*/
	protected function & setErrorMessage($message) {
		# Set
		$this->errorMessage = $message;
		# Chain
		return $this;
	}

	/**
	* put your comment there...
	* 
	*/
	protected abstract function validate();
	
}