<?php
###  Constants
require_once(dirname(__FILE__) .'/LAUNCH.inc.php');

#########################
###  DAX Global Functions

if ( ! isset( $_REQUEST['content_id'] ) ) return trigger_error("You must pass a content_id", E_USER_ERROR);
if ( ! Dax::load()->edit_mode ) {
	///  Exit quietly if this person WAS logged in, and then pinged an XHR after logging out
	if ( ! empty(    $_SESSION )
		&& isset(    $_SESSION['cms-content-admin'] )
		&& is_array( $_SESSION['cms-content-admin'] )
		&& count(    $_SESSION['cms-content-admin'] ) == 0
		) {
		exit;
	}
	return trigger_error("Not Logged In", E_USER_ERROR);
}


// JSON Template : The structure of the content
$cms_profile = ! empty($_REQUEST['cms_profile']) ? $_REQUEST['cms_profile'] : $_REQUEST['content_id'];
$template_json = Dax::load()->getTemplateJSONString($cms_profile);

// Get the actual content for this node
$sect = ContentSection::getPublishedContent($_REQUEST['content_id'],null,true);
if ( ! empty( $sect ) ) {  $initial_content = $sect->content; }
// FIRST-EDIT --> If it doesn't exist, get the default state for this content
else {
	///  Get the default content (1st section's prototype)
	$initial_content = isset($_REQUEST['default_content']) ? $_REQUEST['default_content'] : '{"template":"default"}';
	$json = json_decode($template_json);
	if ( empty($json) ) { trigger_error("Invalid JSON Content in CMS Profile( ". json_error() ." ): ". $template_json_file,E_USER_ERROR); }
	///  Load first template's prototype as the initial_content
	foreach((array) $json as $template_name => $def ) {
		$proto = isset($def->prototype) ? $def->prototype : (object) array();
		$proto->template = $template_name;
		$initial_content = json_encode($proto);
		break;
	}
}
?>

<html>
<head>
	<style type="text/css">
	body {background:none transparent !important;}
	</style>
</head>
<body>
<!-- 	<div style="text-align: center;">
		<button onclick="window.parent.dax_template_cancelled = true; window.parent.close_dax_template_editor();" style="background-color: #gray; color: black;  border: none; font-size: 16px;">Revert and Cancel</button>
		<button onclick="                                             window.parent.close_dax_template_editor();" style="background-color: #dd1c15; color: white; border: none; font-size: 16px;">Save Everything</button>
	</div>
 -->
<?php
$dax_template_config
= (object) array(
	'template_json'   => $template_json,
	'initial_content' => $initial_content,
	'images_base_url' => 'https://'. Dax::load()->config()->uploaded_images_hostname,
	'output_object'   => (! empty($_REQUEST['parent']) ? 'window.parent.dax_editor_output' : trigger_error("No Non-parent mode yet.".E_USER_ERROR)),
	'template_admin_html_preload_js' => Dax::load()->getAdminTemplatesPreloadContent($cms_profile),
	'template_rename_map' => Dax::load()->config()->template_rename_map,
	'advanced_editor_custom_headers' => Dax::load()->config()->advanced_editor_custom_headers,
	'advanced_editor_custom_footers' => Dax::load()->config()->advanced_editor_custom_footers,
	'advanced_editor_custom_angular_modules' => Dax::load()->config()->advanced_editor_custom_angular_modules,
	);
require(APPLICATION_PATH .'/../lib/Dax/js/template-editor/editor.inc.php');

///  Helper function
function json_error() {

	switch (json_last_error()) {
        case JSON_ERROR_NONE:
            return 'No errors';
        break;
        case JSON_ERROR_DEPTH:
            return 'Maximum stack depth exceeded';
        break;
        case JSON_ERROR_STATE_MISMATCH:
            return 'Underflow or the modes mismatch';
        break;
        case JSON_ERROR_CTRL_CHAR:
            return 'Unexpected control character found';
        break;
        case JSON_ERROR_SYNTAX:
            return 'Syntax error, malformed JSON';
        break;
        case JSON_ERROR_UTF8:
            return 'Malformed UTF-8 characters, possibly incorrectly encoded';
        break;
        default:
            return 'Unknown error';
        break;
    }
}

?>
</body>
</html>
