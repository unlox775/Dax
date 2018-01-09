<?php

#########################
###  Prepare Output of HTML headers

# increment this number to force browsers to grab the next version of files
$css_version = 1;
$js_version = 1;

###  Are we in DEBUG mode? (uncompressed javascript for easier debugging)
$this->config()->js_debug_mode = false; # <-- Only change the var temporarily!  A TRUE value is slow and more error-prone!!!
if ( ! $GLOBALS['BUG_ON'] ) $this->config()->js_debug_mode = false; # off no matter what if not on a sandbox!
?>

<?php if ($this->edit_mode) { ?>
    <!-- /////  DAX JavaScript Libraries  ///// -->
    <script type="text/javascript">
      var BUG_ON                = <?php echo ($GLOBALS['BUG_ON']) ? 'true' : 'false' ?>;
      var CSS_VERSION           = '<?php echo $css_version ?>';
      var JS_VERSION            = '<?php echo $js_version ?>';
      var DAX_BASE              = '<?php echo $this->config()->base ?>';

      // function onLive()  { return <?= ($this->env_mode == 'live'  ) ? 'true' : 'false' ?>; };
      // function onBeta()  { return <?= ($this->env_mode != 'live' && $this->env_mode != 'alpha' ) ? 'true' : 'false' ?>; };
      // function onAlpha() { return <?= ($this->env_mode == 'alpha' ) ? 'true' : 'false' ?>; };

      // DAX Editor Configurations
      var dax_editor_launch_mode = '<?php echo $this->config()->editor_launch_mode ?>';
    </script>
    <?php if ( $this->config()->js_lib == 'jquery' ) { ?>
        <?php if ( $this->config()->editor_launch_mode == 'in_lightbox' ) { ?>
            <style type="text/css">
              @import "<?php echo $this->config()->jquery_base?>/jquery.fancybox-1.3.1/fancybox/jquery.fancybox-1.3.1.css?v=<?php echo $js_version?>";
            </style>
        <?php } ?>
        <?php if ( $this->config()->js_debug_mode ) { ?>
            <script src="<?php echo $this->config()->jquery_base?>/jquery.scrollTo-1.4.2.js?v=<?php echo $js_version?>"                         type="text/javascript"></script>
            <script src="<?php echo $this->config()->jquery_base?>/ckeditor/ckeditor.js?v=<?php echo $js_version?>"                             type="text/javascript"></script>
            <?php if ( $this->config()->editor_launch_mode == 'in_lightbox' ) { ?> <script src="<?php echo $this->config()->jquery_base?>/jquery.fancybox-1.3.1/fancybox/jquery.fancybox-1.3.1.pack.js?v=<?php echo $js_version?>" type="text/javascript"></script><?php } ?>
        <?php } else { ?>
            <script src="<?php echo $this->config()->jquery_base?>/jquery.scrollTo-1.4.2-min.js?v=<?php echo $js_version?>"                     type="text/javascript"></script>
            <script src="<?php echo $this->config()->jquery_base?>/ckeditor/ckeditor.js?v=<?php echo $js_version?>"                             type="text/javascript"></script>
            <?php if ( $this->config()->editor_launch_mode == 'in_lightbox' ) { ?> <script src="<?php echo $this->config()->jquery_base?>/jquery.fancybox-1.3.1/fancybox/jquery.fancybox-1.3.1.pack.js?v=<?php echo $js_version?>" type="text/javascript"></script><?php } ?>
        <?php } ?>
        <script src="<?php echo $this->config()->jquery_base?>/../dax-jquery.js?v=<?php echo $js_version?>" type="text/javascript"></script>
        <script src="<?= Dax::load()->config()->base ?>/js/daxcolorbox/jquery.daxcolorbox-min.js"></script>
        <link rel="stylesheet prefetch" href="<?= Dax::load()->config()->base ?>/js/daxcolorbox/daxcolorbox.min.css">
    <?php } else if ( $this->config()->js_lib == 'dojo' ) { ?>
       <!-- /////  Load Dojo  ///// -->
        <style type="text/css">
          @import "<?php echo $this->config()->dojo_base?>/dijit/themes/tundra/tundra.css?v=<?php echo $js_version?>";
          @import "<?php echo $this->config()->dojo_base?>/dijit/themes/dijit.css?v=<?php echo $js_version?>";
          /*@import "<?php echo $this->config()->dojo_base?>/custom/css/littleheroes_tundra/littleheroes_tundra.css?v=<?php echo $js_version?>"; */
        </style>
        <script type="text/javascript">
          var djConfig = {
              //  Set the Dojo debug mode to match the PHP side
              //    This: 1) shows the firebug lite console in IE
              //          2) causes AJAX submit errors to show popups
              isDebug:<?php echo  ($GLOBALS['BUG_ON']) ? 'true' : 'false'?>, parseOnLoad:true
          };
        </script>
        <?php if ( $this->config()->js_debug_mode ) { ?>
            <!--  /////  The UN-COMPRESSED dojo UI stuff, with the custom Littleheroes code as well  ///// -->
            <!--  /////  NOTE: JS Debugging with Firebug doesn't work with the above option    ///// -->
            <script src="<?php echo $this->config()->dojo_base?>/dojo/dojo.js.uncompressed.js?v=<?php echo $js_version?>"                type="text/javascript"></script>
            <script src="<?php echo $this->config()->dojo_base?>/dojo/dojo_dax_cms_editor.js.uncompressed.js?v=<?php echo $js_version?>" type="text/javascript"></script>
        <?php } else { ?>
            <!--  /////  The Pre-compressed dojo UI stuff, with the custom Littleheroes code as well  ///// -->
            <script src="<?php echo $this->config()->dojo_base?>/dojo/dojo.js?v=<?php echo $js_version?>"                type="text/javascript"></script>
            <script src="<?php echo $this->config()->dojo_base?>/dojo/dojo_dax_cms_editor.js?v=<?php echo $js_version?>" type="text/javascript"></script>
        <?php } ?>
    
        <script src="<?php echo $this->config()->dojo_base?>/../dax-dojo.js?v=<?php echo $js_version?>" type="text/javascript"></script>
    <?php } else { trigger_error("Invalid DAX_JS_LIB param: ". $this->config()->js_lib, E_USER_ERROR); } ?>
    <script type="text/javascript">
        // HTML Tag Scrubber Configurations
        var dax_scrub_config = <?php echo $this->config()->scrub_config ?>;
    </script>
    <script src="<?php echo $this->config()->jquery_base?>/../scrub.js?v=<?php echo $js_version?>" type="text/javascript"></script>

<?php } ?>

<link rel="stylesheet" href="<?php echo $this->config()->jquery_base?>/../../css/dax.css?v=<?php echo $css_version?>" media="all" type="text/css"/>
<style type="text/css">
.float_left {
    display: block;
    float: left;
    margin: 0 15px 15px 0;
    font: inherit;
    white-space: inherit;
    letter-spacing: inherit;
}
.float_right {
    display: block;
    float: right;
    margin: 0 0 15px 15px;
    font: inherit;
    white-space: inherit;
    letter-spacing: inherit;
    text-align: left;
}
</style>




<!--  //////  Begin Sidebar and DOM for Edit panes  ///// -->

<a href ="javascript:void(0);" id="dax-sidebar-show" style="display: none">
	<div class="dax-title" >
		CMS Menu
	</div>
	<div class="dax-icon">
		<span class="dax-icon-line"></span>
		<span class="dax-icon-line"></span>
		<span class="dax-icon-line"></span>
	</div>
</a>

<!-- /////  Side-Bar  ///// -->
<div id="dax-sidebar" style="display: none">
	<div class="dax-sidbar-header">
		<a href="javascript:void(0);" class="dax-sidebar-close"><span class="dax-left-arrow"></span></a>

		<img src="/images/logo_kc_steak.png" style="width:75%"/>
		<h2>Content Management</h2>
	</div>

	<!-- /////  Batch Information  ///// -->
	<div class="dax-batch-information dax-row">
		<div class="dax-label-pane">
			<div class="dax-label dax-title">This Batch</div>
			<p class="dax-edit-link">
				<a href="/cms-auth/return">edit</a>
			</p>
		</div>
		<div class="dax-right-pane">
			<div>
				<span class="dax-label">Start:</span>
				<span class="dax-value" title="<?= date('Y-m-d H:i:s T', strtotime( $publish->start_date )) ?>">
					<!-- Use local JS to show time relative to local browser -->
					<?= date('M j', strtotime( $publish->start_date )) ?>
					(<script>document.write(new Date("<?= \App::strToJSFriendlyGMDate($publish->start_date) ?>").toTimeString().substr(0,5).replace(/^0/,''));</script>)

					<? /* date('M j (g:ia)', strtotime( $publish->start_date )) */ ?>
				</span>
			</div>
			<div>
				<span class="dax-label">End:</span>
				<?php if ($publish->end_date && $publish->end_date != '0000-00-00 00:00:00') { ?>
					<span class="dax-value" title="<?= date('Y-m-d H:i:s T', strtotime( $publish->end_date )) ?>">
						<!-- Use local JS to show time relative to local browser -->
						<?= date('M j', strtotime( $publish->end_date )) ?>
						(<script>document.write(new Date("<?= \App::strToJSFriendlyGMDate($publish->end_date) ?>").toTimeString().substr(0,5).replace(/^0/,''));</script>)

						<? /* date('M j (g:ia)', strtotime( $publish->end_date )) */ ?>
					</span>
				<? } else { ?>
					<span class="dax-value"><em>n/a</em></span>
				<? } ?>
			</div>
			<div>
				<span class="dax-label">A/B Channel:</span>
				<span class="dax-value"><em>n/a</em></span>				
			</div>
		</div>
		<div class="dax-clear"></div>
	</div>


	<!-- /////  Page Nav / Create Button Bar  ///// -->
	<div class="dax-nav-create-bar">
		<button class="dax-btn dax-add-new-page-button" onclick="dax_open_new_page_modal()">
			Add New Page
		</button>
		<button class="dax-btn dax-see-all-pages-button" onclick="dax_open_edit_container(<?=str_replace('"','&quot;',json_encode("All Custom Pages - ".$publish->publish_name))?>, '/lib/dax/all_pages.php?content_id='<?=$content_id?>, null, true)">
			See All Pages &rsaquo;&rsaquo;
		</button>
	</div>



	<!-- /////  Page Infomation  ///// -->
	<div class="dax-page-information dax-row">
		<div>
			<div class="dax-label-pane">
				<div class="dax-label dax-title">This Page:</div>
			</div>
			<div class="dax-right-pane dax-title">
				<?= $dax_page_id ?: '/' ?>
			</div>
			<div class="dax-clear"></div>
		</div>
		<div class="narrow-row">
			<div class="dax-label-pane">
				<span class="dax-label">Page Type:</span>
			</div>
			<div class="dax-right-pane"><?= isset(Dax::load()->renderLog()->custom_page_conf) ? 'CMS Custom Page' : 'PHP Phalcon Page' ?></div>
			<div class="dax-clear"></div>
		</div>
		<?php if ( isset(Dax::load()->renderLog()->custom_page_conf) ) { ?>
			<div class="narrow-row">
				<div class="dax-label-pane">
					<span class="dax-label">Page Template:</span>
				</div>
				<div class="dax-right-pane">
					<?php
					$ct = Dax::load()->renderLog()->custom_page_conf->page_template;
					?>
					<select class="dax-form-control"
						onchange="dax_change_page_template(<?= htmlentities(json_encode($dax_page_id)) ?>, $(this).val())"
						>
						<?php foreach ( Dax::load()->customPageTemplates() as $template_code => $label ) { ?>
							<option value="<?= $template_code ?>" <?= $template_code == $ct ? 'selected="selected"' : '' ?>><?= $label ?></option>
						<?php } ?>
					</select>
				</div>
				<div class="dax-clear"></div>
			</div>
		<?php } ?>
	</div>



	<!-- /////  Page Action Buttons  ///// -->
	<div class="dax-page-actions-bar">
		<button class="dax-btn dax-edit-meta-data-button"
			onclick="<?= Dax::load()->template_editor_hook($dax_page_id. '||meta-data','Web Page Meta Data for: '. ($dax_page_id ?: '/'),'Layout/MetaData',0,0,'onclick_only') ?>"
			>
			Edit Meta Data
		</button>
		<? if ( isset(Dax::load()->renderLog()->custom_page_conf) ) { ?>
			<button class="dax-btn dax-delete-page-button"
				onclick="if (confirm('Are you sure you want to delete this page?')) dax_doSaveInput('||deleted||','||CUSTOM_PAGE_URL||<?= $dax_page_id ?>',function() {location.href = '/';});">
				Delete this Page
			</button>
		<? } ?>
	</div>






	<!-- /////  CMS Nodes List Panel  ///// -->
	<div class="dax-anchor">
		<div class="dax-panel">
			<div class="dax-panel-header">
				<div class="dax-title">CMS Nodes on This Page</div>
			</div>
			<div class="dax-panel-body">
				<?php
				$nodes = Dax::load()->renderLog()->nodes;
				?>

				<?php foreach ( $nodes as $content_id => $attrs ) { ?>
					<?php
					list($title,$template) = $attrs;
					if ( empty( $title ) ) { $title = ucfirst(preg_replace('/_/',' ',$content_id)); }
					?>
					<div class="dax-node-line">
						<a href="javascript:;"
							onclick="<?= Dax::load()->template_editor_hook($content_id,$title,$template,0,0,'onclick_only') ?>"
							title="<?= htmlentities($content_id) ?>"
							>
							<?= $title ?>
						</a>
					</div>
				<?php } ?>
			</div>
		</div>
		<div class="dax-panel-buttons">
			<div class="dax-debug-hover" onmouseover="$('.dax-debug-pane').show();" onmouseout="$('.dax-debug-pane').hide();">
				<button class="dax-btn dax-debug-button">Debug</button>
				<div class="dax-debug-pane">
					<div class="dax-title">Heirarchy of Templates</div>
					<?php

					function dax_output_debug_children($children) {
						foreach ( $children as $stack_obj ) {
							echo '<li><span title="'. htmlentities(json_encode($stack_obj->data,JSON_PRETTY_PRINT)) .'">'. $stack_obj->template_code .'</span>';
							if ( ! empty( $stack_obj->child_templates ) ) {
								echo "<ul>";
								dax_output_debug_children($stack_obj->child_templates);
								echo "</ul>";
							}
							echo "</li>";
						}
					}

					?>

					<ul>
						<?php dax_output_debug_children(Dax::load()->renderLog()->template_stack) ?>
<!-- 						<li>
							<span title="{ template: 'foo', ... }">MODULE: content-hero</span>
							foreach ( 
							<ul>
								<li>
									<span title="{ template: 'foo', ... }">TEMPLATE: General/ContentGrid</span>
									<ul>
										<li><span title="{ template: 'foo', ... }">TEMPLATE: General/Banner</span></li>
										<li><span title="{ template: 'foo', ... }">TEMPLATE: Home/Hero</span></li>
										<li><span title="{ template: 'foo', ... }">TEMPLATE: Home/H2Bar</span></li>
										<li><span title="{ template: 'foo', ... }">TEMPLATE: General/OneOneOne</span></li>
									</ul>
								</li>
							</ul>
						</li>
						<li>
							<span title="{ template: 'foo', ... }">MODULE: hero-grid</span>
							<ul>
								<li>
									<span title="{ template: 'foo', ... }">TEMPLATE: General/ContentGrid</span>
									<ul>
										<li><span title="{ template: 'foo', ... }">TEMPLATE: General/Banner</span></li>
										<li><span title="{ template: 'foo', ... }">TEMPLATE: Home/Hero</span></li>
										<li><span title="{ template: 'foo', ... }">TEMPLATE: Home/H2Bar</span></li>
										<li><span title="{ template: 'foo', ... }">TEMPLATE: General/OneOneOne</span></li>
									</ul>
								</li>
							</ul>
						</li> -->
					</ul>
				</div>
			</div>
		</div>
	</div>

	<!-- /////  Sticky Footer  ///// -->
	<div class="dax-sticky-foot">
		<div class="dax-active-bar <?=   $publish->is_published ? 'published' : '' ?>">
			<div class="dax-label-pane" onclick="dax_toggle_published()">
				Published
				<i class="dax-active-toggle-on  fa fa-toggle-on"  aria-hidden="true"></i>
				<i class="dax-active-toggle-off fa fa-toggle-off" aria-hidden="true"></i>
			</div>
			<div class="dax-right-pane">
				<span class="dax-active-on-message" >All changes are LIVE as you make them</span>
				<span class="dax-active-off-message">Please PUBLISH before changes are public</span>
			</div>
			<div class="dax-clear"></div>
		</div>
		<div class="dax-return-button-bar">
			<p class="dax-center dax-info"><em>All Changes are Auto-Saved</em></p>
			<button class="dax-btn dax-return-button" onclick="location.href='/cms-auth/return'">Return to Admin</button>
		</div>
	</div>
</div>

<!-- /////  Edit Iframe  ///// -->
<div id="dax-iframe-container" style="display: none">
	<div class="editor-header">
		<h3 class="editor-title">Editor Title</h3>
		<span class="action-buttons" id="save-true">
			<button onclick="dax_template_cancelled = true; close_dax_template_editor();" style="background-color: #gray; color: black;  border: none; font-size: 16px;">Revert and Cancel</button>
			<button onclick="                               close_dax_template_editor();" style="background-color: #439364; color: white; border: none; font-size: 16px;">Save Everything</button>
		</span>
		<span class="action-buttons" id="save-false">
			<button onclick="dax_template_cancelled = true; close_dax_template_editor(true);" style="background-color: #gray; color: black;  border: none; font-size: 16px;">Close</button>
		</span>
	</div>
	<iframe src="javascript:;" allowtransparency="true"></iframe>
</div>


