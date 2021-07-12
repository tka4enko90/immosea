<?php
/**
* 
*/

# Define namespace
namespace WCFE\Modules\Editor\View\Editor\Templates\Fields\UpgradeAutoCore;

# Input field base
use WCFE\Modules\Editor\View\Editor\Fields\DropDownField;

/**
* 
*/
class Field extends DropDownField {

	/**
	* put your comment there...
	* 
	*/
	public function getText() {
		return $this->__( 'Core' );
	}
	
	/**
	* put your comment there...
	* 
	*/
	public function getTipText() {
		return $this->__( 'The easiest way to manipulate core updates is with the WP_AUTO_UPDATE_CORE constant:' );
	}

	/**
	* put your comment there...
	* 
	*/
	protected function getOptionsList() 
	{
		
		$list = array(
			'true' => $this->__( 'Enable' ),
			'minor' => $this->__( 'Enable only Minor updates' ),
			'false' => $this->__( 'Disable' ),
		);
		
		return $list;
	}

}
