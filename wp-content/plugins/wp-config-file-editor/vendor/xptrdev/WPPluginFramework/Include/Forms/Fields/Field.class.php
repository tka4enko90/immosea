<?php
/**
* 
*/

namespace WPPFW\Forms\Fields;

# Field types
use WPPFW\Forms\Types\IType;
use WPPFW\Forms\Rules\IFieldRule;

/**
* 
*/
abstract class FormField extends FormFieldBase implements IInputField {

	/**
	* put your comment there...
	* 
	* @var mixed
	*/
	protected $default;
	
	/**
	* put your comment there...
	* 
	* @var mixed
	*/
	protected $rules = array();
	
	/**
	* put your comment there...
	* 
	* @var mixed
	*/
	protected $validated;
	
	/**
	* put your comment there...
	* 
	* @var mixed
	*/
	protected $value;

    /**
    * put your comment there...
    * 
    * @param mixed $name
    * @param mixed $rules
    * @param mixed $default
    * @param mixed $value
    * @return FormField
    */
	public function __construct($name, $rules = null, $default = null, $value = null) 
	{
		
		parent::__construct($name);
		
		# Define the set of rules
		if ( is_array( $rules ) ) 
		{
			foreach ( $rules as $rule ) 
			{
				
				$this->setRule($rule);
			}
			
		}
		
		$this->default = $default;
        $this->value = $value;
	}

	/**
	* put your comment there...
	* 
	*/
	public function getRules() {
		return $this->rules;
	}
	
	/**
	* 
	*/
	public function getValue() {
		return $this->value;
	}

	/**
	* put your comment there...
	* 
	*/
	public function hasRules() {
		return !empty($this->rules);
	}

	/**
	* put your comment there...
	* 
	* @param FieldRule $rule
	* @return {FieldRule|FormField}
	*/
	public function & setRule(IFieldRule $rule) {
		# Associate rule with curren field
		$rule->bind($this);
		# Set
		$this->rules[] =& $rule;
		# Chain
		return $this;
	}
	/**
	* put your comment there...
	* 
	* @param mixed $value
	*/
	public function & setValue( $value ) 
	{
		# Cast value
		$this->value = $this->type()->cast( $value ? $value : $this->default );
		
		return $this;
	}

	/**
	* put your comment there...
	* 
	*/
	public function & validate() {
		# Reset Previous validation state
		$this->validated = true;
		# Validate rules
		foreach ($this->getRules() as $rule) {
			# Don't validate next rule unless the previous one is valid
			if (!$rule->isValid()) {
				# Mark as invalid
				$this->validated = false;
				# Get out
				break;
			}
		}
		# Return validation state
		return $this->validated;
	}

}
