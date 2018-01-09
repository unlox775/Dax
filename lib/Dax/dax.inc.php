<?php

###  Load debugging
if ( ! function_exists('bug') ) require_once(dirname(__FILE__) . '/debug.inc.php');
###  Load Model libs
if ( ! class_exists('Stark__ORM') )
{
	require_once(dirname(__FILE__) . '/StarkORM.class.php');
	require_once(dirname(__FILE__) . '/StarkORM/Local.class.php');
}
require_once(dirname(__FILE__) . '/model/ContentSection.class.php');
require_once(dirname(__FILE__) . '/Templates/BaseTemplate.php');
require_once(dirname(__FILE__) . '/Templates/NotFound.php');

class Dax {
	private static $__singleton = null;
	public $edit_mode = null;
    public $dbh = null;
	public $preview_mode = false;
	public $current_daxpub_id = 1;
    public $unique_counter = 1;
	public $empty_scrub_content = '<i>Click here to add Content</i>';
	public $render_log = array(
		'nodes' => array(),
		'template_stack' => array(),
		'template_stack_current' => null
		);

	public $config =
	array(
		'base'            	  	   => '/lib/dax',
		'jquery_base'     	  	   => '[BASE]/js/dax_jquery',
		'dojo_base'       	  	   => '[BASE]/js/dax_dojo',
		'headers_include' 	  	   => '[DOCROOT]/[BASE]/headers.inc.php',
		'footers_include' 	  	   => '[DOCROOT]/[BASE]/footers.inc.php',
		'js_lib'          	  	   => 'jquery',
		'template_base'   	  	   => '[DOCROOT]/[BASE]/templates',
		'template_rename_map'      => array(),
		'custom_page_templates'    => array(),
		'editor_launch_mode'  	   => 'in_place',
		'js_debug_mode'       	   => false,
		'disable_cache'       	   => true,
		'empty_scrub_content' 	   => '<i>Click here to add Content</i>',
		'image_style_default_code' => 'default',
		'uploaded_images_hostname' => '[HTTPHOST]',
		'advanced_editor_custom_headers' => '',
		'advanced_editor_custom_footers' => '',
		'advanced_editor_custom_angular_modules' => array(),
		);

	public function __construct($config = './config.inc.php',$base = null) {
		if ( ! is_null($base) ) $this->config['base'] = $base;

		$this->__load_config($config);

		$this->edit_mode = $this->check_auth();

		$this->meta = (object) [];
	}

	public function renderLog() {
		if ( ! is_object( $this->render_log ) ) { $this->render_log = (object) $this->render_log; }
		return $this->render_log;
	}

	protected function __load_config($config_file) {
		if ( $config_file[0] == '.' && $config_file[1] == '/' ) {
			$config_file = dirname(__FILE__) . substr($config_file,1);
		}
		require($config_file); // Which can now use $this
	}
	public static function load(Dax $set_new = null) {
		if ( ! is_null($set_new) ) { self::$__singleton = $set_new; }
		if ( empty( self::$__singleton ) ) { self::$__singleton = new Dax(); }
		return self::$__singleton;
	}
	public function config($set_key = null, $set_value = '__|||never-set-to-this|||__') {
		///  First time, convert defaults
		if ( is_array($this->config) ){
			foreach( $this->config as $i => $val ) {
				$this->config[$i] = str_replace('[BASE]',    $this->config['base'],     $this->config[$i]);
				$this->config[$i] = str_replace('[DOCROOT]', $_SERVER['DOCUMENT_ROOT'], $this->config[$i]);
				$this->config[$i] = str_replace('[HTTPHOST]', $_SERVER['HTTP_HOST'],    $this->config[$i]);
			}
			$this->config = (object) $this->config;
		}

		///  If they are setting stuff
		if ( isset($set_key) && $set_value != '__|||never-set-to-this|||__' ) {
			if ( is_string($set_value) ) {
				$this->config->$set_key = str_replace('[BASE]',    $this->config->base,       $set_value);
				$this->config->$set_key = str_replace('[DOCROOT]', $_SERVER['DOCUMENT_ROOT'], $set_value);
				$this->config->$set_key = str_replace('[HTTPHOST]', $_SERVER['HTTP_HOST'],    $set_value);
			}
			else { $this->config->$set_key = $set_value; }
		}

		return $this->config;
	}

    public function output_headers() {
        if ( ! $this->edit_mode ) { return; }
        require($this->config()->headers_include); // Which can now use $this
    }
    public function output_footers() {
        if ( ! $this->edit_mode ) { return; }
        require($this->config()->footers_include); // Which can now use $this
    }

	public function inEditMode() { return( ! empty( $this->edit_mode ) ); }
	public function preferCurrentPublish() { return( $this->inEditMode() || $this->preview_mode ); }
    public function sql_now(ContentSection $_tmp_obj) {
        return( Dax::load()->preferCurrentPublish()
            ? '(SELECT start_date FROM '. $_tmp_obj->publish_table .' WHERE daxpub_id = '. $this->current_daxpub_id .')'
            : 'NOW()'
            );
    }

	public function get_dbh() {
        if ( ! empty( $this->dbh ) ) return $this->dbh;

		###  Detect DB Type
		if      ( substr($this->config()->dsn, 0, 6) == 'sqlite' ) { $this->config()->db_type = 'sqlite'; }
		else if ( substr($this->config()->dsn, 0, 5) == 'pgsql'  ) { $this->config()->db_type = 'pg'; }
		else if ( substr($this->config()->dsn, 0, 5) == 'mysql'  ) { $this->config()->db_type = 'mysql'; }
		else {
		    trigger_error("Incompatible DB Type: ". $this->config()->dsn .' in ' . trace_blame_line(), E_USER_ERROR);
		}

		###  Connect to the Database
		START_TIMER('dbh_connect', SQL_PROFILE);
		try {
		    if ( $this->config()->db_type == 'sqlite' && ! file_exists( substr($this->config()->dsn, 7) ) ) {
		        $file = substr($this->config()->dsn, 7);

		        ###  Make the containing dir...
		        $dir = dirname($file);
		        if ( !is_dir($dir) ) mkdir( $dir, 0777, true);

		        $this->config()->init_db_now = true;
		    }
		    $this->dbh = new PDO($this->config()->dsn);
		    if ( $this->config()->db_type == 'pg' ) {
		        if ( $this->config()->pg_schemas != 'public' ) $this->dbh->exec('SET SEARCH_PATH='. $this->config()->pg_schemas);
		        $this->dbh->exec("SET client_encoding TO '". $this->config()->db_encoding ."'");
		    }
		#    if ( $this->config()->db_type == 'sqlite' && ! empty($this->config()->init_db_now) ) {
		        $this->dbh->exec("CREATE TABLE content_section (
		                        content_id character varying(100) NOT NULL,
		                        content text
		                    )");
		#    }
		} catch (PDOException $e) {
		    trigger_error( '"Error Connecting to the database: ' . $e->getMessage() .' in ' . trace_blame_line(), E_USER_ERROR);
		}
		END_TIMER('dbh_connect', SQL_PROFILE);
		return $this->dbh;
	}

	public function get_content($content_id, $with_empty_scrub = false, $prefix = '', $suffix = '') {
		$content = $this->call_user_func_array_cached( array( 'ContentSection', 'getPublishedContent'), array($content_id, $this->get_winner_channel($content_id) ) );
	    if ( empty( $content ) ) {
	        if ( $with_empty_scrub ) return $this->config()->empty_scrub_content;
	        return '';
	    }
	    return $prefix. $content .$suffix;
	}

	public function set_content($content_id, $content) {
		$sect = ContentSection::getPublishedContent($content_id, null, true);

		if ( ! empty( $sect ) && $sect->daxpub_id != $this->current_daxpub_id ) {
			$sect = null;  //  Let it insert a new one...
		}

		###  If it doesn't exist, then create it
		if ( empty( $sect ) ) {
			$sect = new ContentSection();
		    $sect->create(array( 'content_id' => $content_id,
								 'daxpub_id' => $this->current_daxpub_id,
		                         'content' => $content,
		                         ));
		}
		###  Otherwise, update...
		else {
		    $sect->set_and_save(array( 'content' => $content ));
		}
	}

    public function get_winner_channel($content_id, $force = false) {
        ///  Don't do ANY A/B if we are in edit_mode
        ///    NOTE: Preview Mode DOES work so they can test their A/B
        if ( $this->inEditMode() ) return null;

        return null; // Stub.  They need to Define A/B logic

###  Possible Suggestion
#        if ( $force || $this->is_content_multi_channel($content_id) ) {
#            return MyGlobal::getABWinnerOrDiceRollFunction();
#        }
    }

    protected $__is_content_multi_channel_all = null;
    public function is_content_multi_channel($content_id) {
        if ( is_null($this->__is_content_multi_channel_all) ) {
            $this->__is_content_multi_channel_all = $this->call_user_func_array_cached(array('ContentSection','get_all_content_multi_channel'), array());
        }
        return( ! empty($this->__is_content_multi_channel_all[$content_id]) );
    }

	public function check_auth() {
		trace_dump();
		###  Set the session name up here
		session_name('HACK_DAX_DEMO');
		session_start();

		return( isset($_SESSION['auth_success']) ? $_SESSION['auth_success'] : false );
	}

	public function cache_hook_enabled() {
		///  Disabled cache for the Content Admin
		return( ! $this->config()->disable_cache && ! $this->check_auth() ? true : false );
	}

	/// Disabled by default
	public function call_user_func_array_cached($func, $params) {
		//  In case they are sloppy...
		if ( !is_array( $params ) ) $params = array( $params );

		//  Run function with caching.
#		if ( $this->cache_hook_enabled() ) {
#			$return = LocalCache::doCache($func, $params );
#		}
#		else {
			$return = call_user_func_array($func, $params);
#		}
		return $return;
	}

	public function has_content($content_id) {
		$content = $this->call_user_func_array_cached( array( 'ContentSection', 'getPublishedContent'), array($content_id) );
	    if ( empty( $content ) ) {
	        return false;
	    }
	    return true;
	}


	public function module( $type, $content_id, $prefix = '', $suffix = '', $style = '', $no_edit = '', $content_backup = false ) {
	    if ( ! $this->edit_mode && $type == 'json') return '';
	    if ( !$this->edit_mode || $no_edit ) return $this->get_content($content_id, false, $prefix, $suffix);

	    ###  Only certain module types, please
	    if ( ! in_array($type, array('input','textarea','json','richtext')) ) trigger_error("Bad content module type '". $type ."' in " . trace_blame_line(), E_USER_ERROR);


	    return ( '  <span id="dax_editable-'. $content_id .'" class="tundra dax_editable dax_editable-'. $type .'"'
	             .        ( $this->config()->editor_launch_mode == 'in_lightbox' && $type == 'richtext'
	                        ? ' onClick="dax_triggerLightBox('."'". $content_id ."'".','."'". $type ."'".')"'
	                        : ' onClick="dax_editContent('    ."'". $content_id ."'".','."'". $type ."'".')"'
	                        )
	             .        ( ! empty( $style ) ? ' editable_style="'. htmlentities($style) .'"' : '' )
	             .       '>'
	             .     '<span class="dax_editable_buttons"><a no-href="javascript:dax_editContent('."'". $content_id ."'".','."'". $type."'".')">Click to Edit</a></span>'
	             .     ( ! empty( $prefix ) ? '<span class="dax_editable_prefix">'. $prefix .'</span>' : '' )
	             .     '<span id="dax_editable_content-'. $content_id .'" class="dax_editable_content" no-onClick="dax_editContent('."'". $content_id ."'".','."'". $type ."'".')""'. ( $content_backup ? ' content_backup="'. htmlentities($this->get_content($content_id, true)) .'"' : '') .'>'
	             .          $this->get_content($content_id, true)
	             .     '</span>'
	             .     ( ! empty( $suffix ) ? '<span class="dax_editable_suffix">'. $suffix .'</span>' : '' )
	             . '</span>'
	             );
	}

	public function image_upload( $content_id, $extra_attrs = '', $default_img = 'dax_brokem_img.png', $istyle_code = '', $container_extra_attrs = '') {
	    if ( empty($istyle_code) ) $istyle_code = $this->config()->image_style_default_code;

	    ###  Get the content and override with default either way...
	    $img_url = $this->get_content($content_id, true, '','');
	    if ( $img_url == $this->empty_scrub_content ) $img_url = $default_img;

	    $img_tag = ( '<img id="dax_editable-image-'. $content_id
	                 . '" src="'. $img_url
	                 . '" '. $extra_attrs
	                 . '/>'
	                 );

	    ###  Return here if NOT EDIT mode
	    if ( ! $this->edit_mode) return $img_tag;

	    ###  If EDIT mode, add the upload floater...
	    return ( '<div class="dax_editable-image_container"'. $container_extra_attrs .'>'
	             .     $img_tag
	             .     '<a class="kill-parent-a-links" href="javascript:void(null)">'
	             .         '<div id="dax_editable-'. $content_id .'"'
	             .             ' class="tundra dax_editable dax_editable-image dojoxEditorUploadNorm"'
	             .             ' >'
	             .             '<div dojoType="dojox.form.FileUploader"'
	             .                  ' hoverClass="dax_editable-image"'
	             .                  ' activeClass="dax_editable-image"'
	             .                  ' pressClass="dax_editable-image"'
	             .                  ' disabledClass="dax_editable-image"'
	             .                  ' selectMultipleFiles="false"'
	             .                  ' uploadOnChange="true"'
	             .                  ' showProgress="true"'
	             .                  ' serverTimeout="15000"'
	             .                  ' style="width: 16px; height: 16px; background:url('. $this->config()->base .'/images/upload_image.png) 0 0 no-repeat;"'
	             .                  ' uploadUrl="/istyle/upload.php'. (! empty($istyle_code) ? '?istyle_code='. $istyle_code : '') .'"'
	             .                  ' id="dax_editable_content-'. $content_id .'"'
	             .                  ' class="dax_editable_content dax_editable_content-'. $content_id .'"'
	             .                  ' >'
	             .                  '.'
	             .             '</div>'
	             .         '</div>'
	             .     '</a>'
	             .     '<div class="clear"></div>'
	             . '</div>'
	             );
	}

	public function get_template_content($content_id, $title = '', $cms_profile = '', $no_edit_hook = false, $default_content = null){
		$raw = $this->get_content($content_id);
		if ( empty($raw) && ! empty($default_content) ) { $raw = $default_content; }
		$template_data = json_decode($raw);
		$this->renderLog()->nodes[$content_id] = array($title, $cms_profile);
	    if ( ! $no_edit_hook ) echo $this->template_editor_hook($content_id, $title, $cms_profile);
		if ( empty( $template_data ) ) {
			if ( ! empty( $raw ) ) { bug('DAX Template: invalid JSON content', $template_data, $raw); }
			return '<div></div>';
		}
		if ( ! is_object($template_data) || empty( $template_data->template ) ) { bug('DAX Template ['. $content_id .']: JSON content was malformed ['. $raw .']'); }

		$template = new Dax__Template($this,$template_data->template,$this->templateClassInstance($template_data->template), $template_data);
		//set the unique key so others have access to it.
		$template->__content_id = $content_id;
		return $template->render();
	}


	public function template_editor_hook($content_id, $title = '', $cms_profile = '', $x_adj = 0, $y_adj = 0, $hook_style = 'triangle',$label = 'Edit', $default_content = null) {
	    if ( !$this->edit_mode ) return '';

		$this->renderLog()->nodes[$content_id] = array($title, $cms_profile);

	    $hook_id = 'dax-template_editor_hook-'. $this->unique_counter++;

	    $link = (
	    	$this->config()->base . "/edit-template.php?content_id=" . urlencode($content_id)
	    	."&cms_profile=". urlencode($cms_profile)
	    	."&default_content=". urlencode($default_content)
	    	. "&parent=1"
	    	);
	    $edit_js = (
	    	"dax_open_edit_container("
	    	. htmlentities(json_encode($title)) .","
	    	. htmlentities(json_encode($link)) .", "
	    	. htmlentities(json_encode($content_id))
	    	 .");");

	    if ( $hook_style == 'onclick_only' ) { return $edit_js; }
	    elseif ( $hook_style == 'simple_link' ) {
	    	return ('<a href="javascript:void(null)"'
	    		.		" onClick=\"". $edit_js ."\""
		        .   '>'. $label
		        .   '</a>'
	    		);

	    }
	    elseif ( $hook_style == 'tag' ) {
		    return (
		        '<div id="'. $hook_id .'" style="position:absolute; top: 0px; right: 0px; z-index: 10000; width: 400px">'
		        .   '<div style="position: relative;background-color: #585DB3;display: inline-block;border: 3px solid #3D429F;border-top: none;" title="'. $title .'">'
		        .     '<a style="display: block;text-decoration: none;" href="javascript:;"'
		        .       " onclick=\"dax_template_cancelled = false; ". $edit_js ."\""
		        .   '><span style="display: block;padding: 2px 27px 2px;color: white;font-weight: bold;">'. $label .'</span>'
		        .   '</a>'
		        .   '</div>'
		        . '</div>'
		        . '<script>dax_DOMReady(function(){dax_reposition("'. $hook_id .'",'. (int) $x_adj .','. (int) $y_adj .');});</script>'
		        );
	    }
	    else {
		    return (
		        '<div id="'. $hook_id .'" style="position:absolute; top: 0px; right: 0px; width: 50px; height: 50px; z-index: 10000"><div style="position: relative" title="'. $title .'">'
		        .   '<div style="position:absolute; top: -1px; right: -1px; width: 0;height: 0;border-style: solid;border-width: 0 53px 53px 0;border-color: transparent #217000 transparent transparent;"></div>'
		        .   '<div style="position:absolute; top: 0px; right: 0px;width: 0;height: 0;border-style: solid;border-width: 0 50px 50px 0;border-color: transparent #80ec59 transparent transparent; z-index: 10001"></div>'
		        .   '<a style="display: block; position:absolute; top: 0px; right: 0px; width: 50px; height: 50px; text-align: right; color: black; text-decoration: none; z-index: 10002" href="javascript:;"'
		        .     " onclick=\"dax_template_cancelled = false;  ". $edit_js ."\""
		        . '><span style="display: block; padding: 6px 3px 0 0; color: black">'. $label .'</span></a>'
		        . '</div></div>'
		        . '<script>dax_DOMReady(function(){dax_reposition("'. $hook_id .'",'. (int) $x_adj .','. (int) $y_adj .');});</script>'
		        );
		}
	}

	public function get_template_model($class) {
		# Stub
	}

	public function get_distict_page_id($my_SERVER = null, $my_REQUEST = null) {
		if ( is_null($my_SERVER ) ) $my_SERVER  = $_SERVER;
		if ( is_null($my_REQUEST) ) $my_REQUEST = $_REQUEST;

		return rtrim(preg_replace('/\?.+$/','',$my_SERVER['REQUEST_URI']),'/');
	}

	protected $__matches_custom_page_routing_all = null;
	protected $__matches_custom_page_routing_all_ci = null;
	public function matches_custom_page_routing($my_SERVER = null, $my_REQUEST = null) {
        if ( is_null($this->__matches_custom_page_routing_all) ) {
            //get_all_custom_pages returns an array of custom page names in two arrays, 1 case sensitive, 2 case insensitive.
        	$custom_pages =
	            $this->call_user_func_array_cached(
	            	array('ContentSection','get_all_custom_pages'),
	            	array($this->get_winner_channel('||IGNORED||',true), true)
	            	);
	            $this->__matches_custom_page_routing_all = $custom_pages[0];
	            $this->__matches_custom_page_routing_all_ci = $custom_pages[1];
        }

        //first see if we have a match as is
        if(!empty($this->__matches_custom_page_routing_all[$this->get_distict_page_id($my_SERVER,$my_REQUEST)])){
        	return true;
        }
        //next see if we have a case insensitive match, if so redirect to the proper case.
        if(!empty($this->__matches_custom_page_routing_all_ci[strtolower($this->get_distict_page_id($my_SERVER,$my_REQUEST))])){
        	$redirect_to = $this->__matches_custom_page_routing_all_ci[strtolower($this->get_distict_page_id($my_SERVER,$my_REQUEST))];
			header("HTTP/1.1 301 Moved Permanently");
			header('Location:'. $redirect_to);
			die();
        }
        return false;
    }

    public function getTemplateJSONString($template_code) {
    	///  Get the profile's Template class and it's self-JSON as a base
    	$class = $this->loadTemplateClass($template_code);
    	$json = (object) array( $template_code => $class::selfJSONObject() );

    	///  Loop through all child templates and load their JSON as well
    	foreach ( $class::getSubTemplatesRecursive() as $sub_template_code ) {
    		$sub_class = $this->loadTemplateClass($sub_template_code);
    		$json->$sub_template_code = $sub_class::selfJSONObject();
    	}

    	return json_encode($json, JSON_PRETTY_PRINT);
    }

    public function templateCodeParse($template_code) {
    	$template_code = str_replace('\\','/',$template_code); // just in case they are comfused
    	$class =  '\Dax\Templates\\'. str_replace('/','\\',$template_code);
    	return array($template_code,$class);
    }
    public function templateParentClass($class) {
    	$parent_class = get_parent_class($class);
    	if ( ! $parent_class || ! preg_match('/^\\\?Dax\\\Templates\\\(.+)$/', $parent_class, $m) ) { return array(false,false); }
    	return array(str_replace('\\','/',$m[1]),$parent_class);
    }
    public function loadTemplateClass($template_code) {
    	list($template_code,$class) = $this->templateCodeParse($template_code);
    	if ( class_exists($class) ) { return $class; } // Already loaded?

    	$file = $this->config()->template_base .'/'. $template_code .'.php';
    	if ( ! file_exists($file) ) {
    		throw new Dax__TemplateNotFound__Exception("Dax : Template class file not found: ". $file);
    	}
    	require_once($file);
    	if ( ! class_exists($class) ) { throw new \Exception("Dax : Class did not exist after loading template file (mispelled class name?)"); }
    	return $class;
    }
    public function templateClassInstance($template_code) {
    	list($template_code,$class) = $this->templateCodeParse($template_code);
    	$this->loadTemplateClass($template_code);
    	return new $class($template_code, $this);
    }

    public function getAdminTemplatesPreloadContent($template_code) {
    	list($template_code,$class) = $this->templateCodeParse($template_code);

    	$scripts = array();

    	$template = $this->templateClassInstance($template_code);
    	$scripts[] = 'angular.module("cmsEditApp").run(function($templateCache){$templateCache.put("/dax-virtual-preload/templates/'.$template_code.'.html",'.json_encode($template->adminHTML()).')});';

    	///  Loop through all child templates and load their JSON as well
    	foreach ( $class::getSubTemplatesRecursive() as $sub_template_code ) {
    		$sub_template = $this->templateClassInstance($sub_template_code);
    		$scripts[] = 'angular.module("cmsEditApp").run(function($templateCache){$templateCache.put("/dax-virtual-preload/templates/'.$sub_template_code.'.html",'.json_encode($sub_template->adminHTML()).')});';
    	}

    	return join("\n",$scripts);
    }

    public function customPageTemplates() {
    	$return = array();
    	foreach ( $this->config()->custom_page_templates as $template_code ) {
    		$class = $this->loadTemplateClass($template_code);
    		$return[$template_code] = $class::$__admin_display_name;
    	}

    	return $return;
    }
}

class Dax__Exception extends Exception {  }
class Dax__TemplateNotFound__Exception extends Exception {  }

class Dax__Template {
	public $dax = null;
	public $__name = null;
	public $__original_data = null;
	public $__template_obj = null;
	public $__content_id = null;
	public static $__meta = array();
	public function __construct($dax, $name, $template_obj, $data) {
		$this->__original_data = $data;
		foreach ( (array) $data as $k => $v ) {
			$this->$k = $v;
		}
		$this->dax = $dax;
		$this->__name = $name;
		$this->__template_obj = $template_obj;
		$this->meta = (object) self::$__meta;
	}
	public function render() {
		$stack_obj = (object) array(
			'template_code' => $this->__template_obj->__self_template_code,
			'__parent' => $this->dax->renderLog()->template_stack_current,
			'child_templates' => array(),
			'data' => $this->__original_data,
			);
		if ( $stack_obj->__parent === null ) { $this->dax->renderLog()->template_stack[]      = $stack_obj; }
		else                                 { $stack_obj->__parent->child_templates[] = $stack_obj; }
		$this->dax->renderLog()->template_stack[] = $this->__template_obj->__self_template_code;
		$this->dax->renderLog()->template_stack_current = $stack_obj;

		echo "<!-- START DAX::TEMPLATE ".$this->__template_obj->__self_template_code. " -->";
		$this->__template_obj->echoFrontHTML($this);
		echo "<!-- END DAX::TEMPLATE ".$this->__template_obj->__self_template_code. " -->";

		$this->dax->renderLog()->template_stack_current = $stack_obj->__parent;
		unset($stack_obj->__parent);
		array_pop($this->dax->renderLog()->template_stack);
	}

	public function template($template_data) {
		if ( empty( $template_data ) || ! is_object($template_data) || empty( $template_data->template ) ) { bug('DAX Template: JSON content was malformed'); return false; }

		$template = $template_data->template;
		if ( isset( $this->dax->config()->template_rename_map[$template] ) ) {
			$template = $this->dax->config()->template_rename_map[$template];
		}
		try {
			$template_obj = Dax::load()->templateClassInstance($template);
		}
		catch (Dax__TemplateNotFound__Exception $e) {
			$template_obj = Dax::load()->templateClassInstance('NotFound');
			$template_obj->__intended_template_code = $template;
		}
		$template_obj->meta = $this->meta;

		$template = new Dax__Template($this->dax,$template_data->template, $template_obj,$template_data);
		$template->__content_id = $this->__content_id;
		$template->__parent = $this;
		return $template->render();
	}

	// public function model($class) {
	// 	return $this->dax->get_template_model($class);
	// }


}
