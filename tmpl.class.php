/**
 *
 * @author TAMER.ELNAHAL
 * @abstract This is a simple and efficient class that make your application compatible with the most famous templates engines.
 */
class cls_tmpl {
	public  $Obj;
	private $sEngine;
	private $aMethod = array ();
	private $aData = array ();
	
	/**
	 *
	 * @return unknown
	 */
	public function __construct($aConf) {
		$this->sEngine = $aConf ['engine'];
		require_once ($aConf ['tmpl_eng'] [$aConf ['engine']]);
		$this->{$this->sEngine} ( $aConf );
	}
	
	/**
	 * 
	 * @param array $aConf
	 */
	private function tmplvar(&$aConf) {
		$this->Obj = new Template ();
		$this->aMethod ['add'] = 'AddParam';
		$this->aMethod ['output'] = 'fetch';
	}
	
	/**
	 * 
	 * @param array $aConf
	 */
	private function smarty(&$aConf) {
		$this->Obj = new Smarty ();
		$this->aMethod ['add'] = 'assign';
		$this->aMethod ['output'] = 'fetch';
				
		$this->Obj->template_dir = $aConf ['tmpl_dir'];
		$this->Obj->config_dir = $aConf ['tmpl_dir'] . 'configs/';
		$this->Obj->compile_dir = $aConf ['cache_dir'] . 'compile/';
		$this->Obj->cache_dir = $aConf ['cache_dir'] . 'tmp/';
		$this->Obj->caching = $aConf ['cache'];
	}
	
	/**
	 * 
	 * @param array $aConf
	 */
	private function mustache(&$aConf) {
		Mustache_Autoloader::register ();
		// use .tmpl instead of .mustache for defau$this->sAddMethodlt template extension
		$options = array (
				'extension' => $aConf ['extension'] 
		);
		$aConf ['cache_dir'] .= 'tmp/cache/mustache';
		
		$aMustacheConf = array (
				'template_class_prefix' => '__MyTemplates_',
				'extension' => $aConf ['extension'],
				'cache' => $aConf ['cache_dir'],
				'cache_file_mode' => 0666, // Please, configure your umask instead of doing this :)
				'cache_lambda_templates' => true,
				'loader' => new Mustache_Loader_FilesystemLoader ( $aConf ['tmpl_dir'], $options ),
				'partials_loader' => new Mustache_Loader_FilesystemLoader ( $aConf ['tmpl_dir'] . 'inc', $options ),
				'helpers' => array (
						'i18n' => function ($text) {
							// do something translatey here...
						} 
				),
				'escape' => function ($value) {
					return $value;
					// return htmlspecialchars( $value, ENT_COMPAT, 'UTF-8' );
				},
				'charset' => 'UTF-8',
				'logger' => new Mustache_Logger_StreamLogger ( 'php://stderr' ),
				'strict_callables' => true 
		);
		
		$this->Obj = new Mustache_Engine ( $aMustacheConf );
		$this->aMethod ['output'] = 'render';
	}
	
	/**
	 * 
	 */
	private function addToTmplEng() {
		foreach ( $this->aData as $sName => $mValues ) {
			$this->Obj->{$this->aMethod ['add']} ( $sName, $mValues );
		}
		unset ( $this->aData );
	}
	
	/**
	 * 
	 * @param mix $mNames
	 * @param string $mValues
	 */
	public function add($mNames, $mValues = NULL) {
		if (is_array ( $mNames )) {
			$this->aData = array_merge ( $this->aData, $mNames );
		} else {
			$this->aData [$mNames] = $mValues;
		}
	}
	
	/**
	 * 
	 * @param string $sName
	 * @return multitype:
	 */
	public function get($sName = NULL) {
		return (! is_null ( $sName ) && isset ( $this->aData [$sName] )) ? $this->aData [$sName] : $this->aData;
	}
	
	/**
	 * 
	 * @param unknown $sPath
	 * @return string
	 */
	public function output($sPath) {
		if ($this->sEngine == 'mustache') {
			
			$tpl = $this->Obj->loadTemplate ( basename ( $sPath ) ); // loads __DIR__.'/views/foo.mustache';
			return $tpl->render ( $this->aData );
			
			return $this->Obj->{$this->aMethod ['output']} ( file_get_contents ( $sPath ), $this->aData );
		}
		$this->addToTmplEng ();
		return $this->Obj->{$this->aMethod ['output']} ( $sPath );
	}
}
