<?php
/**
* 
*/

namespace WPPFW\Services\Queue;

/**
* 
*/
abstract class ScriptsQueue extends Resources {
	
	/**
	* 
	*/
	const JQUERY = 'jquery';
	
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
    * @var mixed
    */
    protected $localizationNameSuffix;
    
    /**
    * put your comment there...
    * 
    * @var mixed
    */
    protected $localized = array();
    
    
    /**
    * put your comment there...
    * 
    * @var mixed
    */
    protected $nameSuffix;
    
    /**
    * put your comment there...
    * 
    * @var \WPPFW\MVC\View\Base
    */
    protected $view;
    
    /**
    * put your comment there...
    * 
    * @param mixed $txt
    */
    public function __( $txt )
    {
        return $this->getView()->__( $txt );
    }

    /**
    * put your comment there...
    * 
    * @param \WPPFW\MVC\View\Base $view
    * @param mixed $nameSuffix
    * @param mixed $localizeNameSuffix
    * @return ScriptsQueue
    */
    public function __construct( \WPPFW\MVC\View\Base & $view, $nameSuffix, $localizeNameSuffix )
    {
        parent::__construct();
        
        $this->view =& $view;
        $this->nameSuffix = $nameSuffix;
        $this->localizationNameSuffix = $localizeNameSuffix;
    }

    /**
    * put your comment there...
    * 
    * @param ScriptResource $script
    * @param mixed $localized
    * @return Resource
    */
	public function & add( ScriptResource & $script, $localized = false ) 
    {
        
        $this->localized[ $script->getName() ] = $localized;
        
		return $this->addStore( $script );
	}

    /**
    * put your comment there...
    * 
    */
    public function & getView()
    {
        return $this->view;
    }

    /**
    * put your comment there...
    * 
    * @param mixed $name
    */
	protected function wpEnqueue( $name ) 
    {
		# Equeue Wordpress script
		wp_enqueue_script( $name );
        
        # if localized? Enqueue Script localization.
        # Avoid named resource (Wordpress built-in), they don't 
        # added to the store so they don't have localization queue
        if ( isset( $this->localized[ $name ] ) && $this->localized[ $name ] )
        {
            
            $script =& $this->store[ $name ];
            # JavaScript var name is the root namespace + Resource class name + L10N           
            $resourceClassNameStruct = explode( '\\', get_class( $script ) );
            
            $jsVarName = "{$resourceClassNameStruct[ 0 ]}{$resourceClassNameStruct[ count( $resourceClassNameStruct) - 1 ]}L10N";
            
            # Import localization file and get result array
            $localizationFile = $script->getPath() . DIRECTORY_SEPARATOR . basename( $script->getFileName(), ".{$this->nameSuffix}" ) . ".{$this->localizationNameSuffix}";
            
            $localization = require $localizationFile;
            
            # Localize
            wp_localize_script( $name, $jsVarName, $localization );
        }
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
		$script =& $object;
		# Equeue Wordpress script
		wp_register_script(
			$script->getName(), 
			$script->getUrl(), 
			$script->dependencies()->getArray(), 
			$script->getVersion(), 
			$script->getLocation());
	}

}
	