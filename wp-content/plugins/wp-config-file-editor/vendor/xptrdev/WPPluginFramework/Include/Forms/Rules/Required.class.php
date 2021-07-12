<?php
/**
* 
*/

# Define namespace
namespace WPPFW\Forms\Rules;

/**
* 
*/
class RequiredField extends FieldRule 
{
	
    /**
    * 
    */
    const MSG_CANNOT_EMPTY = 'cannot_empty';
    
	/**
	* put your comment there...
	* 
	*/
	public function validate() 
    {
        
		$valid = true;
        
		# If invalid set to false with error message set
		if ( ! $this->getValue() ) 
        {
            
			$valid = false;
            
			$this->setErrorMessage( $this->getMessageString( self::MSG_CANNOT_EMPTY ) );
		}
        
		return $valid;
	}
    
    /**
    * put your comment there...
    * 
    * @param mixed $message
    */
    protected function getMessageString( $message )
    {

        switch ( $message )
        {
            case self::MSG_CANNOT_EMPTY:
            
                return 'Cannot be empty';
            
            break;
            
            default:
            
            $string = false;
        }
        
        return $string;
    }

}