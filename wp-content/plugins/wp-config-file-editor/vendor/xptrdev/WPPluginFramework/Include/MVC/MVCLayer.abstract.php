<?php
/**
* 
*/

namespace WPPFW\MVC;

# Imports
use WPPFW\MVC\IMVCServiceManager;

/**
* 
*/
abstract class MVCComponenetsLayer implements IMVCComponentsLayer {
	
	/**
	* put your comment there...
	* 
	* @var mixed
	*/
	private $serviceManager;
	
    /**
    * put your comment there...
    * 
    * @param mixed $txt
    * @param args list
    */
    public function __( $txt )
    {
        return call_user_func_array( array( $this->l10n(), '_' ), func_get_args() );
    }
    
	/**
	* put your comment there...
	* 
	* @param IMVCServiceManager $factory
	* @return {MVCComponenetsLayer|IMVCServiceManager}
	*/
	public function __construct(IMVCServiceManager $serviceManager) {
		# Initialize
		$this->serviceManager =& $serviceManager;
	}

    /**
    * put your comment there...
    * 
    * @param mixed $txt
    * @param mixed $args
    */
    protected function _e( $txt )
    {
        echo call_user_func_array( array( $this, '__' ), func_get_args() );
    }
    
    /**
    * put your comment there...
    * 
    */
    protected function & l10n()
    {
        return $this->serviceManager->getExtension( 'l10n' );
    }
    
	/**
	* put your comment there...
	* 
	* @return \WPPFW\MVC\MVCDispatcher
	*/
	protected function & mvcServiceManager() {
		return $this->serviceManager;
	}
	
}

