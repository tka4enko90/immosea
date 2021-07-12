<?php
/**
* 
*/

namespace WPPFW\Services\Queue;

/**
* 
*/
abstract class StylesQueue extends Resources {
	
	/**
	* 
	*/
	const JQUERY_UI_TABS = 'jquery-ui-tabs';
	
	/**
	* 
	*/
	const THICK_BOX = 'thickbox';
	
	/**
	* put your comment there...
	* 
	* @param ScriptResource $script
	*/
	public function & add(StyleResource & $style) {
		# Add to queues list
		return $this->addStore($style);
	}

	/**
	* put your comment there...
	* 
	* @param mixed $object
	*/
	protected function wpEnqueue($name) {	
		# Equeue Wordpress script
		wp_enqueue_style($name);
	}
	
	/**
	* put your comment there...
	* 
	* @param mixed $object
	*/
	protected function wpRegister(& $object) {
		# Polymorphism using Comments!
		/**
		* put your comment there...
		* 
		* @var ScriptResource
		*/
		$style =& $object;
		# Equeue Wordpress script
		wp_register_style(
			$style->getName(), 
			$style->getUrl(), 
			$style->dependencies()->getArray(), 
			$style->getVersion()
			);
	}

}
	