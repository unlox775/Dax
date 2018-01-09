<!--[if lt IE 7]>
  <p class="browsehappy">You are using an <strong>outdated</strong> browser. Please <a href="http://browsehappy.com/">upgrade your browser</a> to improve your experience.</p>
<![endif]-->
<?php
	$template_dax_js_base = Dax::load()->config()->base ."/js/";
	$template_editor_base = $template_dax_js_base ."template-editor/";
?>
<!-- ///  REMOTE Library : FontAwesome  /// -->
<link href="//maxcdn.bootstrapcdn.com/font-awesome/4.2.0/css/font-awesome.min.css" rel="stylesheet">
<!-- ///  REMOTE Library : JQuery  /// -->
<script src="//ajax.googleapis.com/ajax/libs/jquery/1.11.1/jquery.min.js"></script>
<!-- ///  REMOTE Library : AngularJS  /// -->
<script src="//ajax.googleapis.com/ajax/libs/angularjs/1.5.7/angular.js"></script>
<script src="//ajax.googleapis.com/ajax/libs/angularjs/1.5.7/angular-route.js" ></script>
<script src="//ajax.googleapis.com/ajax/libs/angularjs/1.5.7/angular-sanitize.js"></script>
<!-- ///  Local Library : Bootstrap  /// -->
<script type="text/javascript"   src="<?= $template_editor_base ?>lib/bootstrap.min.js"></script>
<link rel="stylesheet prefetch" href="<?= $template_editor_base ?>lib/bootstrap.min.css">
<!-- ///  Local Library : Colorbox (re-prefixed to not collide with other colorbox installs)  /// -->
<script src="<?= $template_dax_js_base ?>daxcolorbox/jquery.daxcolorbox-min.js"></script>
<link rel="stylesheet prefetch" href="<?= $template_dax_js_base ?>daxcolorbox/daxcolorbox.min.css">
<!-- ///  Local Library : DropZone  /// -->
<script src="<?= $template_editor_base ?>lib/dropzone/dropzone.min.js"></script>
<script src="<?= $template_editor_base ?>lib/dropzone/idropzone.js"></script>
<link rel="stylesheet" href="<?= $template_editor_base ?>lib/dropzone/dropzone.css">
<!-- ///  Local Library : textAngular (Richtext Editing)  /// -->
<script src="<?= $template_editor_base ?>lib/textAngular/dist/textAngular-rangy.min.js"></script>
<script src="<?= $template_editor_base ?>lib/textAngular/dist/textAngularSetup.js"></script>
<script src="<?= $template_editor_base ?>lib/textAngular/dist/textAngular-sanitize.js" ></script>
<script src="<?= $template_editor_base ?>lib/textAngular/dist/textAngular.js"></script>
<link rel="stylesheet" type="text/css" href="<?= $template_editor_base ?>lib/textAngular/dist/textAngular.css">

<!-- <script src="//m-e-conroy.github.io/angular-dialog-service/javascripts/dialogs.min.js" type="text/javascript"></script> -->

<?php

/// Customer Header content
echo Dax::load()->config()->advanced_editor_custom_headers;

?>

<!-- ///  Application : Editor Components  /// -->
<script type="text/javascript">
var cms_advanced_editor_custom_angular_modules = <?= json_encode($dax_template_config->advanced_editor_custom_angular_modules) ?>;
</script>
<script src="<?= $template_editor_base ?>app.js"></script>
<script src="<?= $template_editor_base ?>controllers/MainCtrl.js"></script>
<script src="<?= $template_editor_base ?>directives/template-chooser.js"></script>
<script src="<?= $template_editor_base ?>directives/template-include.js"></script>
<script src="<?= $template_editor_base ?>directives/add-to-array-bar.js"></script>
<link rel="stylesheet" type="text/css" href="<?= $template_editor_base ?>lib/editor.css"/>
<script>
	<?= $dax_template_config->template_admin_html_preload_js ?>
</script>

<!-- /// Config variables passed to Angular App  /// -->
<script type="text/javascript">
var cms_template_editor_base = <?= json_encode($template_editor_base) ?>;
var cms_edit_template_json = <?= $dax_template_config->template_json ?>;
var cms_edit_initial_content = <?= str_replace('</script','<\/script',$dax_template_config->initial_content) ?>;
var cms_edit_images_base_url = '<?= $dax_template_config->images_base_url ?>';
<?= $dax_template_config->output_object ?> = {'saved_by_cms_editor': false};
var cms_edit_output_object = <?= $dax_template_config->output_object ?>;
var cms_template_rename_map = <?= json_encode($dax_template_config->template_rename_map) ?>;

//  Interval to keep-fresh the Save Data-Transfer Mechanism
<? if ( ! empty( $_REQUEST['parent'] ) ) { ?>
	$(document).ready(function(){
		setInterval(function(){
			angular.element($('#cms-edit-output-json')[0]).triggerHandler('click');
		},200);
	});
<? } ?>
</script>

<!-- ///  Angular App Base Markup  /// -->
<div id="cmsEditApp" ng-app="cmsEditApp">
	<div ng-controller="MainCtrl as foo">
		<template-chooser template-data="local" suppress-content-tile="true"></template-chooser>
		<!--  ///  Save Data-Transfer Mechanism  /// -->
		<textarea ng-click="saveJSONContent()" id="cms-edit-output-json" style="width: 100%; height: 500px; display: none">{{local | json}}</textarea>
	</div>
</div>

<? /* / ?>
<!-- TODO: Move these to CSS / JS File... -->
<style type="text/css">
#cmsEditApp h1 {
	font-size: 21px;
	margin: 10px 0 0 5px;
	color: #ee1c25;
}
#cmsEditApp h1 > .collapse-icon {
	display: inline-block;
	width: 16px;
	height: 16px;
	background: url(/images/open.gif) 0 0 no-repeat;
	margin-right: 7px;
}
#cmsEditApp h1.closed > .collapse-icon {
	background: url(/images/closed.gif) 0 0 no-repeat;
}

#cms-edit-saved {
	display: none;
	font-size: 15px;
	color: green;
}
</style>
<? /* */ ?>
<?php

/// Customer Header content
echo Dax::load()->config()->advanced_editor_custom_footers;

?>
