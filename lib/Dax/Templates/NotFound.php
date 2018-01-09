<?php

namespace Dax\Templates;

class NotFound extends \Dax\Templates\BaseTemplate {
	public $__intended_template_code = null;

	public static $__admin_display_name = "Template Not Found";
	public static $__properties_and_defaults = array(
		'copy' => '',
		);
	// public static $__sub_templates = array(
	// 	);
	// public static $__hide_name_and_toggle = true;
	// public static $__default_template = "General/PlainText";

	public function echoFrontHTML($local) {
		if ( \Dax::load()->edit_mode ) { echo '<div style="border: 2px dashed red; padding: 15px; margin-top: 10px"><b>DAX ERROR: Template Not Found: '. $this->__intended_template_code  ."</b></div>"; }
		else { echo "<!-- DAX ERROR: Template Not Found: ". $this->__intended_template_code  ." -->"; }

	}

	public function adminHTML() {
		echo "Template Not Found: ". $this->__intended_template_code;
	}
}
