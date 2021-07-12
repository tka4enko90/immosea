<?php
/**
* 
*/

namespace WPPFW\Plugin;

/**
* 
*/
class Localization 
{
    
    /**
    * put your comment there...
    * 
    * @var mixed
    */
    protected $plugin;
    
    /**
    * put your comment there...
    * 
    * @var mixed
    */
    protected $textDomain;

    /**
    * put your comment there...
    * 
    * @param mixed $txt
    * @param mixed $args
    */
    public function _( $txt )
    {
        $args = func_get_args();
        
        $t_text = __( $txt, $this->getTextDomain() );
        
        $args[ 0 ] = $t_text;
        
        return call_user_func_array( 'sprintf', $args );
    }
    
    /**
    * put your comment there...
    * 
    * @param mixed $textDomain
    * @param PluginBase $plugin
    * @return {Localization|PluginBase}
    */
    public function __construct( $textDomain, PluginBase & $plugin )
    {
        $this->textDomain = $textDomain;
        $this->plugin =& $plugin;
    }

    /**
    * put your comment there...
    * 
    * @param mixed $textDomain
    * @param PluginBase $plugin
    * @return {Localization|PluginBase}
    */
    public static function & localize( $textDomain, PluginBase & $plugin )
    {        
            
        $localization = new Localization( $textDomain, $plugin );
        
        $localization->load();
        
        return $localization;
    }
    
    /**
    * put your comment there...
    * 
    */
    public function & getPlugin()
    {
        return $this->plugin;
    }
    
    /**
    * put your comment there...
    * 
    */
    public function getTextDomain()
    {
        return $this->textDomain;
    }
    
    /**
    * put your comment there...
    * 
    */
    public function load()
    {
        // Get localization directory
        $config =& $this->plugin->getConfig()->getPlugin();
        $localizationDirName = $config[ 'parameters' ][ 'localizationDir' ];
        
        $localizationPath = $this->plugin->getName() . DIRECTORY_SEPARATOR . $localizationDirName;
        
        // Load localizasation
        return load_plugin_textdomain( $this->textDomain, null, $localizationPath );
    }
    
}
