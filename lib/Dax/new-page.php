<?php
###  Constants
require_once(dirname(__FILE__) .'/LAUNCH.inc.php');

#########################
###  DAX Global Functions

$dax = Dax::load();

if ( ! $dax->edit_mode )           return trigger_error("Not Logged In", E_USER_ERROR);

$template_dax_js_base = $dax->config()->base ."/js/";
$template_editor_base = $template_dax_js_base ."template-editor/";

if ( ! empty($_POST) && ! empty($_POST['new_page_url']) ) {
	$new_distinct_page_id = preg_replace('/[\s-]+/','-',$_POST['new_page_url']);
	if ( $new_distinct_page_id[0] != '/' ) { $new_distinct_page_id = '/'. $new_distinct_page_id; }
	$new_distinct_page_id = $dax->get_distict_page_id(['REQUEST_URI' => $new_distinct_page_id]);

	///  If page already exists, just bounce to it...
	$cust_page_content_id = '||CUSTOM_PAGE_URL||'. $new_distinct_page_id;
	$existing_page = $dax->get_content($cust_page_content_id);
	if ( $existing_page && $existing_page != '||deleted||' ) { bounceToURL($new_distinct_page_id); }

	$page_template = $_POST['new_page_template'];
	$template = $dax->templateClassInstance($page_template);

	///  Otherwise, create it
	$dax->set_content($cust_page_content_id, json_encode(['page_template' => $page_template]));

	///  Stuff it with default content
	$page_content_id = $new_distinct_page_id. '||page_content-'. $page_template;
	$prototype = (object) $template->prototype();
	$prototype->template = $page_template;
	$dax->set_content($page_content_id, json_encode($prototype));

	///  Set Meta Page Title
	if ( ! empty($_POST['new_page_title']) ) {
		$page_title = $_POST['new_page_title'];
		$page_meta_id = $new_distinct_page_id. '||meta-data';
		$dax->set_content($page_meta_id, json_encode(['template' => 'General/MetaData', 'title' => $page_title]));
	}

	bounceToURL($new_distinct_page_id);
}

function bounceToURL($url) {
	echo "<script>window.parent.location.href = ". json_encode($url) .";</script>"; exit;
}
?>
<!-- ///  REMOTE Library : JQuery  /// -->
<script src="//ajax.googleapis.com/ajax/libs/jquery/1.11.1/jquery.min.js"></script>
<!-- ///  Local Library : Bootstrap  /// -->
<script type="text/javascript"   src="<?= $template_editor_base ?>lib/bootstrap.min.js"></script>
<link rel="stylesheet prefetch" href="<?= $template_editor_base ?>lib/bootstrap.min.css">
<script type="text/javascript">
	$(document).ready(function() {
		$('#new_page_url').focus();
	});
</script>


<form class="form-horizontal" method="POST" action="?action=submit">
	<div class="container-fluid">
		<h2>New Page Information</h2>

		<div class="form-group">
			<label for="Page URL" class="col-sm-3 control-label">Page URL</label>
			<div class="col-sm-9">
				<input name="new_page_url" class="form-control" id="new_page_url" placeholder="/my-new-page/" />
			</div>
		</div>
		<div class="form-group">
			<label class="col-sm-3 control-label">Page Template</label>
			<div class="col-sm-9">
				<select name="new_page_template" class="form-control">
					<?php foreach ( Dax::load()->customPageTemplates() as $template_code => $label ) { ?>
						<option value="<?= $template_code ?>"><?= $label ?></option>
					<?php } ?>
				</select>
			</div>
		</div>
		<div class="form-group">
			<label for="Page URL" class="col-sm-3 control-label">Page Title</label>
			<div class="col-sm-9">
				<input name="new_page_title" class="form-control" id="new_page_title" placeholder="e.g. Example Page Title" />
			</div>
		</div>
		<div class="form-group">
			<div class="col-sm-offset-3 col-sm-9">
				<button type="submit" class="btn btn-default">Create new Page</button>
			</div>
		</div>
	</div>
</form>
