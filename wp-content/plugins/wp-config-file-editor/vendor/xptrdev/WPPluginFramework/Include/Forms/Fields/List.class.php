<?php
/**
* 
*/

namespace WPPFW\Forms\Fields;

/**
* 
*/
class FormListField extends FormFieldsList {

	/**
	* put your comment there...
	* 
	* @param IField $field
	*/
	public function & add(IField $field) {
		# Add
		$this->addChain($field);
		# Return element
		return $field;
	}

	/**
	* put your comment there...
	* 
	* @param IField $field
	*/
	public function & addChain(FormFieldBase $field) 
	{
		
		$field->setParent( $this );
		
		
		$this->fields[$field->getName()] = $field;
		

		return $this;
	}

	/**
	* put your comment there...
	* 
	* @param mixed $name
	* @return IField
	*/
	public function & get($name)
    {
		$field =    isset($this->fields[$name]) ?
                    $this->fields[$name] :
                    null;
        
        return $field;
	}
	
	/**
	* put your comment there...
	* 
	* @param mixed $value
	*/
	public function & setValue( $values ) 
	{
		
		$values = $this->type()->cast($values);
		
		
		$fields =& $this->getFields();
		
		# Set list values.
		foreach ( $fields as $index => $field ) 
		{
			# Getting field value
			$value = isset( $values[ $index ] ) ? $values[ $index ] : null;
			
			# Setting field value
			$field->setValue( $value );
		}
		
		return $this;
	}

}