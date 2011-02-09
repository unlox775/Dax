<?php

#########################
###  Prepare Output of HTML headers

# increment this number to force browsers to grab the next version of files
$css_version = 1;
$js_version = 1;

###  Are we in DEBUG mode? (uncompressed javascript for easier debugging)
$DAX_JS_DEBUG_MODE = false; # <-- Only change the var temporarily!  A TRUE value is slow and more error-prone!!!
if ( ! $BUG_ON ) $DAX_JS_DEBUG_MODE = false; # off no matter what if not on a sandbox!
?>

<?php if ($EDIT_DAX_MODE) { ?>
    <!-- /////  DAX JavaScript Libraries  ///// -->
    <script type="text/javascript">
      var BUG_ON                = <?php echo ($BUG_ON) ? 'true' : 'false' ?>;
      var CSS_VERSION           = '<?php echo $css_version ?>';
      var JS_VERSION            = '<?php echo $js_version ?>';
      var DAX_BASE              = '<?php echo $DAX_BASE ?>';

      function onLive()  { return <?= ($ENV_MODE == 'live'  ) ? 'true' : 'false' ?>; };
      function onBeta()  { return <?= ($ENV_MODE != 'live'
                                    && $ENV_MODE != 'alpha' ) ? 'true' : 'false' ?>; };
      function onAlpha() { return <?= ($ENV_MODE == 'alpha' ) ? 'true' : 'false' ?>; };
    </script>
    
    <script type="text/javascript">
      // DAX Editor Configurations
      var dax_editor_launch_mode = '<?php echo $DAX_EDITOR_LAUNCH_MODE ?>';
    </script>
    <?php if ( $DAX_JS_LIB == 'jquery' ) { ?>
        <?php if ( $DAX_EDITOR_LAUNCH_MODE == 'in_lightbox' ) { ?>
            <style type="text/css">
              @import "<?php echo $DAX_JQUERY_BASE?>/jquery.fancybox-1.3.1/fancybox/jquery.fancybox-1.3.1.css?v=<?php echo $js_version?>";
            </style>
        <?php } ?>
        <?php if ( $DAX_JS_DEBUG_MODE ) { ?>
            <script src="<?php echo $DAX_JQUERY_BASE?>/jquery-1.4.2.js?v=<?php echo $js_version?>"                                  type="text/javascript"></script>
            <script src="<?php echo $DAX_JQUERY_BASE?>/jquery.scrollTo-1.4.2.js?v=<?php echo $js_version?>"                         type="text/javascript"></script>
            <script src="<?php echo $DAX_JQUERY_BASE?>/ckeditor/ckeditor.js?v=<?php echo $js_version?>"                             type="text/javascript"></script>
            <?php if ( $DAX_EDITOR_LAUNCH_MODE == 'in_lightbox' ) { ?> <script src="<?php echo $DAX_JQUERY_BASE?>/jquery.fancybox-1.3.1/fancybox/jquery.fancybox-1.3.1.pack.js?v=<?php echo $js_version?>" type="text/javascript"></script><?php } ?>
        <?php } else { ?>
            <script src="<?php echo $DAX_JQUERY_BASE?>/jquery-1.4.2.min.js?v=<?php echo $js_version?>"                              type="text/javascript"></script>
            <script src="<?php echo $DAX_JQUERY_BASE?>/jquery.scrollTo-1.4.2-min.js?v=<?php echo $js_version?>"                     type="text/javascript"></script>
            <script src="<?php echo $DAX_JQUERY_BASE?>/ckeditor/ckeditor.js?v=<?php echo $js_version?>"                             type="text/javascript"></script>
            <?php if ( $DAX_EDITOR_LAUNCH_MODE == 'in_lightbox' ) { ?> <script src="<?php echo $DAX_JQUERY_BASE?>/jquery.fancybox-1.3.1/fancybox/jquery.fancybox-1.3.1.pack.js?v=<?php echo $js_version?>" type="text/javascript"></script><?php } ?>
        <?php } ?>
        <script src="<?php echo $DAX_JQUERY_BASE?>/../dax-jquery.js?v=<?php echo $js_version?>" type="text/javascript"></script>
    <?php } else if ( $DAX_JS_LIB == 'dojo' ) { ?>
       <!-- /////  Load Dojo  ///// -->
        <style type="text/css">
          @import "<?php echo $DAX_DOJO_BASE?>/dijit/themes/tundra/tundra.css?v=<?php echo $js_version?>";
          @import "<?php echo $DAX_DOJO_BASE?>/dijit/themes/dijit.css?v=<?php echo $js_version?>";
          /*@import "<?php echo $DAX_DOJO_BASE?>/custom/css/littleheroes_tundra/littleheroes_tundra.css?v=<?php echo $js_version?>"; */
        </style>
        <script type="text/javascript">
          var djConfig = {
              //  Set the Dojo debug mode to match the PHP side
              //    This: 1) shows the firebug lite console in IE
              //          2) causes AJAX submit errors to show popups
              isDebug:<?php echo  ($BUG_ON) ? 'true' : 'false'?>, parseOnLoad:true
          };
        </script>
        <?php if ( $DAX_JS_DEBUG_MODE ) { ?>
            <!--  /////  The UN-COMPRESSED dojo UI stuff, with the custom Littleheroes code as well  ///// -->
            <!--  /////  NOTE: JS Debugging with Firebug doesn't work with the above option    ///// -->
            <script src="<?php echo $DAX_DOJO_BASE?>/dojo/dojo.js.uncompressed.js?v=<?php echo $js_version?>"                type="text/javascript"></script>
            <script src="<?php echo $DAX_DOJO_BASE?>/dojo/dojo_dax_cms_editor.js.uncompressed.js?v=<?php echo $js_version?>" type="text/javascript"></script>
        <?php } else { ?>
            <!--  /////  The Pre-compressed dojo UI stuff, with the custom Littleheroes code as well  ///// -->
            <script src="<?php echo $DAX_DOJO_BASE?>/dojo/dojo.js?v=<?php echo $js_version?>"                type="text/javascript"></script>
            <script src="<?php echo $DAX_DOJO_BASE?>/dojo/dojo_dax_cms_editor.js?v=<?php echo $js_version?>" type="text/javascript"></script>
        <?php } ?>
    
        <script src="<?php echo $DAX_DOJO_BASE?>/../dax-dojo.js?v=<?php echo $js_version?>" type="text/javascript"></script>
    <?php } else { trigger_error("Invalid DAX_JS_LIB param: ". $DAX_JS_LIB, E_USER_ERROR); } ?>
    <script type="text/javascript">
        // HTML Tag Scrubber Configurations
        var dax_scrub_config = <?php echo $DAX_SCRUB_CONFIG ?>;
    </script>
    <script src="<?php echo $DAX_JQUERY_BASE?>/../scrub.js?v=<?php echo $js_version?>" type="text/javascript"></script>

<?php } ?>

<link rel="stylesheet" href="<?php echo $DAX_DOJO_BASE?>/../../css/dax.css?v=<?php echo $css_version?>" media="all" type="text/css"/>
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
