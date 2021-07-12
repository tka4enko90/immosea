<?php
/**
* 
*/

namespace WPPFW\Forms\Fields;

# Important
use WPPFW\Forms\Types\IType;
use WPPFW\Forms\IForm;

/**
* 
*/
abstract class FormFieldBase implements IField {
	
	/**
	* put your comment there...
	* 
	* @var mixed
	*/
	protected $filters;
	
	/**
	* put your comment there...
	* 
	* @var mixed
	*/
	protected $form;
	
	/**
	* put your comment there...
	* 
	* @var mixed
	*/
	protected $name;
	
    /**
    * put your comment there...
    * 
    * @var mixed
    */
    protected $params = array();
    
	/**
	* put your comment there...
	* 
	* @var mixed
	*/
	protected $parent;
	
	/**
	* put your comment there...
	* 
	* @var mixed
	*/
	protected $rules;
	
	/**
	* put your comment there...
	* 
	* @var mixed
	*/
	protected $type;
	
	/**
	* put your comment there...
	* 
	* @var mixed
	*/
	protected $validated;
	
	/**
	* put your comment there...
	* 
	* @param mixed $name
	* @return FormFieldBase
	*/
	public function __construct($name) {
		# Initialize
		$this->name =& $name;
		$this->type = $this->getType();
	}
	
	/**
	* put your comment there...
	* 
	*/
	public function & getForm() {
		return $this->form;
	}
	
	/**
	* put your comment there...
	* 
	*/
	public function getName() {
		return $this->name;
	}

    /**
    * put your comment there...
    * 
    * @param mixed $name
    */
    public function getParam($name)
    {
        return isset($this->params[$name]) ? $this->params[$name] : null;
    }
    
	/**
	* put your comment there...
	* 
	*/
	public function & getParent()
	{
		return $this->parent;
	}
	
	/**
	* put your comment there...
	* 
	*/
	public function getPath()
	{
		
		$path = array();
		
		$parent = $this;
		
		while ( $parent )
		{
			$path[] = $parent->getName();
			
			$parent = $parent->getParent();
		}
		
		$path = array_reverse( $path );
		
		$path = implode( '/', $path );
		
		return $path;
	}
	
	/**
	* put your comment there...
	* 
	*/
	protected abstract function getType();

	/**
	* put your comment there...
	* 
	* @param IForm $form
	* @return IForm
	*/
	protected function & setForm(IForm & $form) {
		# Set form
		$this->form =& $form;
		# Chain
		return $this;
	}
	
    /**
    * put your comment there...
    * 
    * @param mixed $name
    * @param mixed $value
    */
    public function & setParam($name, $value)
    {
        
        $this->params[$name] = $value;
        
        return $this;
    }
    
	/**
	* put your comment there...
	* 
	* @param mixed $parent
	*/
	public function & setParent( $parent )
	{
		
		$this->parent =& $parent;
		
		return $this;
	}

	/**
	* put your comment there...
	* 
	*/
	public function & type() {
		return $this->type;
	}

}
