<?php

namespace Dax\Templates;

class BaseTemplate {
	public $__self_template_code = null;
	public $dax = null;
	public $meta = null;
	public static $__admin_display_name = "Template Base Default";
	public static $__properties_and_defaults = array();
	public static $__sub_templates = array();
	public static $__hide_name_and_toggle = false;
	public static $__default_template = "General/PlainText";
	public $local = null; // used in front template redering

	public function __construct($self_template_code, $dax) {
		$this->__self_template_code = $self_template_code;
		$this->dax = $dax;
	}

	public static function selfJSONObject() {
		$self = get_called_class();
		$json = array(
			'name'                 => $self::$__admin_display_name,
			'hide_name_and_toggle' => $self::$__hide_name_and_toggle,
			'sub_templates'        => $self::$__sub_templates,
			'prototype'            => $self::prototype(),
			'default_template'     => $self::$__default_template,
			);
		return (object) $json;
	}
	public static function prototype() {
		$self = get_called_class();
		return $self::$__properties_and_defaults;
	}

	public static function getSubTemplatesRecursive(&$seen_classes = array(), &$recursive_return_value = array()) {
		$self = get_called_class();
		$seen_classes[] = $self;
		foreach ( $self::$__sub_templates as $template_code ) {
			$sub_class = \Dax::load()->loadTemplateClass($template_code);
			if ( ! in_array($template_code,$seen_classes) ) {
				$recursive_return_value[] = $template_code;
				///  Recurse
				$sub_class::getSubTemplatesRecursive($seen_classes,$recursive_return_value);
			}
		}
		return $recursive_return_value;
	}

	public function adminHTML() {
		list($template_code,$class) = $this->dax->templateCodeParse($this->__self_template_code);

		$found_template_file = false;
		while(1) {
			$admin_html_file = $this->dax->config()->template_base .'/'. $template_code .'/admin.html';
			if ( file_exists($admin_html_file) ) { $found_template_file = true; break; }
			///  Fall back to parent class
			else {
				list($template_code,$class) = $this->dax->templateParentClass($class);
				if ( ! $template_code ) { break; }
			}
		}
		if ( ! $found_template_file ) { return '<div><b>[Dax Admin template not found: '. $this->__self_template_code .'/admin.html' .']</b></div>'; }

		return file_get_contents($admin_html_file);
	}

	public function echoFrontHTML($dax_template) {
		list($template_code,$class) = $this->dax->templateCodeParse($this->__self_template_code);

		$front_html_file = null;
		$found_template_file = false;
		while(1) {
			$front_html_file = $this->dax->config()->template_base .'/'. $template_code .'/front.phtml';
			if ( file_exists($front_html_file) ) { $found_template_file = true; break; }
			///  Fall back to parent class
			else {
				list($template_code,$class) = $this->dax->templateParentClass($class);
				if ( ! $template_code ) { break; }
			}
		}
		if ( ! $found_template_file ) { echo '<div style="color: red"><b>[EDIT MODE -- Front template not found: '. $this->__self_template_code .'/front.html' .']</b></div>'; }

		$this->local = $dax_template;
		return include($front_html_file);
	}

	public function localToJson() {
		$to_json = (object) [];

		if ( empty($this->local) || ! is_object($this->local) ) { return '{}'; }

		foreach ( (array) $this->local as $k => $v ) {
			if ( is_object($v) && get_class($v) != 'stdClass' ) { continue; }
			if ( $k == 'meta' ) { continue; }
			$to_json->$k = $v;
		}

		return safe_json_encode($to_json);
	}


	/////////////////////////
	///  Template rendering cycle

	public function template($template_data) {
		if ( is_object($this->local) ) { return $this->local->template($template_data); }
		throw new \Exception("Cannot call template when not within a render cycle (no-local param)");
	}
	public function model($class) {
		return $this->dax->get_template_model($class);
	}
}


///  Helper Functions
if ( ! function_exists('safe_json_encode') ) { function safe_json_encode($value){
    if (version_compare(PHP_VERSION, '5.4.0') >= 0) {
        $encoded = json_encode($value, JSON_PRETTY_PRINT);
    } else {
        $encoded = json_encode($value);
    }
    switch (json_last_error()) {
        case JSON_ERROR_NONE:
            return $encoded;
        case JSON_ERROR_DEPTH:
            return 'Maximum stack depth exceeded'; // or trigger_error() or throw new Exception()
        case JSON_ERROR_STATE_MISMATCH:
            return 'Underflow or the modes mismatch'; // or trigger_error() or throw new Exception()
        case JSON_ERROR_CTRL_CHAR:
            return 'Unexpected control character found';
        case JSON_ERROR_SYNTAX:
            return 'Syntax error, malformed JSON'; // or trigger_error() or throw new Exception()
        case JSON_ERROR_UTF8:
            $clean = utf8ize($value);
            return json_encode($clean);
        default:
            return 'Unknown error'; // or trigger_error() or throw new Exception()

    }
} }

if ( ! function_exists('utf8ize') ) { function utf8ize($mixed) {
    if (is_array($mixed)) {
        foreach ($mixed as $key => $value) {
            $mixed[$key] = utf8ize($value);
        }
    }else if (is_object($mixed)) {
        foreach ((array) $mixed as $key => $value) {
        	if ( substr($key,0,1) == "\0" ) { continue; }
            $mixed->$key = utf8ize($value);
        }
    } else if (is_string ($mixed)) {
        return utf8_encode($mixed);
    }
    return $mixed;
} }
