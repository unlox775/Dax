<?php require_once($_SERVER['DOCUMENT_ROOT'] . '/dax/dax.inc.php'); ?>
<html>
<head>
    <?php require_once($_SERVER['DOCUMENT_ROOT'] . $DAX_HEADERS_INCLUDE); ?>
</head>
<body>

<!-- /////  DAX Login Example  ///// -->
<div style="float: right; width: 550px; background: #eee; margin: 0 0 20px 20px; padding: 10px">
    <form method="POST"
          action="/dax/login.php"
          >
        <input type="hidden" name="redirect" value="/"/>
        Log in to Edit Content:<br/>
        Username: <input type="text" name="username" value="dax"/><br/>
        Password <input type="password" name="password" value=""/> (Hint: the password is 123qwe)<br/>
        <input type="submit" value="Activate DAX Edit Mode"/>
    </form>

    <h3>Login Status:</h3>
    You are
    <? if ( $EDIT_DAX_MODE ) { ?>
        LOGGED IN<br/><br/>
        
        <a href="/dax/logout.php">Log Out</a>
    <? } else { ?>
        NOT LOGGED IN
    <? } ?>
</div>

<!-- /////  Documentation  ///// -->
<h2>DAX - Next-Gen Content Management System</h2>
<p>
    This is the DAX content management system!
    <b><u>Installation can be as simple as grabbing the source of this
    website.</b></u>

</p>
<h3>Try it out!</h3>
<p>
    Here are some Examples of pages you can edit:

    <ul>
        <li><a href="apple_ipad_features.php">DAX iPad Page</a> - This is a copy of Apple.com's iPad Features Page, but completely DAX-enabled</li>
    </ul>
</p>
<h3>Why create an Edit-in-Place CMS?!</h3>
<p>
    DAX lets the End-User switch to "EDIT MODE" then just browse
    around their web site and edit their content right in-place.
    This lets them edit headers, images, and large blocks of
    text.  We've found that modern web design doesn't fit the
    bill of a single content area per web page.  And because
    layout can be complex and flow in a very custom style, it's
    almost impossible for the CMS to actually be able to change
    and/or control the flow of items on the page.
</p>
<p>
    So, to heck with it!  Lets leave layout in the hands of the
    experts (Us), and let the clients worry about what to put in
    our layout.  Joomla and even wordpress are allowing clients
    to edit more than one content block per page from their Admin
    area.  However, this style offers Nothing in the way of
    WYSIWYG; usually the style for the text they just entered
    cannot be viewed in-context until they publish and go visit
    the web page.
</p>
<p>
    Another advantage of Edit-in-Place are DAX's Image Upload
    Modules, which let you replace an image instantly and
    in-context.  Using the <a
    href="http://istyle.dax-dev.com/">Image Style System</a>,
    we are also able to have instant advanced compositing of the
    image they just uploaded to frame, sharpen, crop or more.
</p>
<h3>How it works</h3>
<p>
    <ol>
        <li>Install the "dax" folder, and the "istyle" folders</li>
        <li>Edit the config files (unless you just put them in the docroot, then you're all ready to go)</li>
        <li>Provide some way to log in and out.  The above form source is all that is needed.  You can put this in some un-listed URL at minimum.</li>
        <li>
            Then, For each page that you want editable add this to the very top (if it does SESSION, it needs to do headers):
            <pre>
                &lt;?php require_once($_SERVER['DOCUMENT_ROOT'] . '/dax/dax.inc.php'); ?&gt;
            </pre>
            Add add this to the head tag:
            <pre>
                &lt;?php require_once($_SERVER['DOCUMENT_ROOT'] . $DAX_HEADERS_INCLUDE); ?&gt;
            </pre>
        </li>
        <li>It stores all the text strings (and image paths) in a SQLite DB in /dax/sqlite/</li>
    </ol>
</p>
<hr style="clear:both"/>

<h2>Examples (Go ahead and edit these...)</h2>
<h3>Basic Input Editing Module</h3>
<?= dax_module('input','home_demo_input') ?>

<h3>Textarea Editing Module</h3>
<?= dax_module('textarea','home_demo_textarea') ?>

<h3>Rich Text Editing Module</h3>
<?= dax_module('richtext','home_demo_richtext') ?>

<h3>Image Upload Module</h3>
<?= dax_image_upload( 'home_demo_img',
                      'width="192" height="145" alt="iPad with Wi-Fi + 3G. Learn more."',
                      'http://images.apple.com/ipad/features/images/nav_promo_3g_20100430.jpg',
                      'apple/nav_promo'
                      ) ?>
