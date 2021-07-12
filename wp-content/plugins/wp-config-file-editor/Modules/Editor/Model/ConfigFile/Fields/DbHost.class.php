<?php
/**
* 
*/

# Define namespace
namespace WCFE\Modules\Editor\Model\ConfigFile\Fields;

/**
* 
*/
class DbHost extends Constant {
  
  /**
  * put your comment there...
  * 
  * @var mixed
  */
	protected $comments = array
	(
		'MySQL hostname'
	);

	/**
	* put your comment there...
	* 	
	* @var mixed
	*/
	protected $name = 'DB_HOST';
	
	/**
	* put your comment there...
	* 
	*/
	protected function getType() {
		return new Types\StringType();
	}

	/**
	* put your comment there...
	* 
	*/
	public function getValue()
	{
		
		$value = parent::getValue();
		
		if ( $port = $this->form->get( 'DbPort' )->getValue() )
		{
			$value .= ":{$port}";
		}

		return $value;
	}
}

