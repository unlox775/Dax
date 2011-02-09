var tmpCurrentDAXModuleSavedContent = '';
var uploadImageSuffix = '';
var dax_inEditorStyleSheets = '';
var dax_inEditorSimulateClasses = [];

var dax_scrubTimer = null;
function dax_editContent(contentId,type) {
    rootDiv = dojo.byId("dax_editable-"+ contentId);
    // Skip out if they are running this on onr that is already focused
    if ( dojo.hasClass(rootDiv,'dax_editable-current_focus') ) return;

    // Make sure all others are closed...
    dax_findAndCloseAllFocusedContentSections()

    //  Make sure our Editor hacks are in place...
    applyDojoHacks();

    //  If this content section is already focused then skip out
    if ( dojo.hasClass(rootDiv,'dax_editable-current_focus') ) return false;
    editable_style = dojo.attr(rootDiv,'editable_style') || '';

    contentDiv = dojo.byId("dax_editable_content-"+ contentId);
    //  Reset the "Click here to add content message"
    if ( contentDiv.innerHTML.match(/^\s*<i>\s*Click here to add Content\s*<\/i>\s*$/i) ) contentDiv.innerHTML = '';

    dojo.addClass(rootDiv,'dax_editable-current_focus');

    //  For later race condition testing
    raceConditionFlags[contentId] = 0;

    if ( type == 'richtext' ) {
        tmpCurrentDAXModuleSavedContent = contentDiv.innerHTML;
        contentDiv.innerHTML = '<div id="dax_editable_content_editor-'+ contentId +'">'+ contentDiv.innerHTML +'</div>';


        //  Widget options
        widget_options = {
            plugins: [ 'undo','redo',
                       '|','bold','italic','underline',
                       '|','createLink','unlink','insertHorizontalRule',
                       '|','insertOrderedList','insertUnorderedList',
                       '|', // 'insertImage',
                       { name:'dojox.editor.plugins.UploadImage',
                         command:'uploadImage', 
                         uploadUrl: '/istyle/upload.php'+ uploadImageSuffix,
                         selectMultipleFiles: false,
                         iconClassPrefix:'Edit',
                         fileMask: ["All Images", "*.jpg;*.jpeg;*.gif;*.png"],
                         degradable: true
                       },
                       {name:'dijit._editor.plugins.FontChoice', command:'formatBlock',  custom: ['noFormat','p','h2','h3','img class="float_none"','img class="float_left"','img class="float_right"'], generic:true}
                     ],
            height: '320px', // Default height, otherwise it shows 0 height...
            focusOnLoad: true,
            styleSheets: dax_inEditorStyleSheets

//            contentPreFilters: [
//                function(/* String */ html){
//                    console.log(2);
//                    console.log(html);
//                    newHtml = html.replace(/^\s*<style>[^\<]+<\/style>\s*<div class="main-content">\s*/,'');
//                    if ( newHtml != html ) newHtml = newHtml.replace(/\s*<\/div>\s*$/,'');
//                    console.log(newHtml);
//                    console.log(2.5);
//                  return ( "<style>\n"
//                             +"    @import '"+ DAX_BASE +"/css/reset.css?v="+   CSS_VERSION +"';\n"
//                             +"    @import '"+ DAX_BASE +"/css/content.css?v="+ CSS_VERSION +"';\n"
//                             +"    @import '"+ DAX_BASE +"/css/editor.css?v="+  CSS_VERSION +"';\n"
//                             +"</style>\n"
//                             +     '<div class="main-content">' + newHtml + '</div>'
//                           ); // String
//                    console.log(3);
//              }
//            ],
        };
        
        

        //  Extract the width and height
        if ( editable_style.match(/height *: *([0-9]+px)/i) ) {
            height = ( editable_style.match(/height *: *([0-9]+px)/i) )[1];
            if (height) widget_options.height = height;
        }
        if ( editable_style.match(/width *: *([0-9]+px)/i) ) {
            width = ( editable_style.match(/width *: *([0-9]+px)/i) )[1];
            if (width) widget_options.width = width;
        }

        var editor = new dijit.Editor(widget_options, dojo.byId('dax_editable_content_editor-'+ contentId));

        // Set up the Save Mechanism...
        var hasFileUploader = false;  dojo.forEach(widget_options.plugins, function (item) { if ( dojo.isObject(item) && item.command == 'uploadImage') hasFileUploader = true; });
        if ( hasFileUploader ) {
            //  Create a Close link that will serve instead of onBlur and onChange events (which don't work with fileUploader)
            //    We do a setTimeout, because in a switch-to-another-editor, the race condition kills the save button
            createHackDojoEditorCloseLink = function() {
                //  Create a floating A
                closeLink = dojo.create("a");
                dojo.addClass(closeLink,'hackDojoEditorCloseLink');
                closeLink.innerHTML = 'Save';
                closeLink.style.cssText = 'border: black solid 1px; float: right; font-weight: bold; color: white; background-color:red;';
                dojo.byId('dax_editable-'+contentId).insertBefore(closeLink,dojo.byId('dax_editable-'+contentId).firstChild);
                dojo.connect(closeLink, 'onclick', null, function(e) { dax_saveInput(editor.getValue(),contentId); this.style.display = 'none';} );
            };
            setTimeout(createHackDojoEditorCloseLink, 500);

        }
        else {
            // Connect Events
            dojo.connect(editor, 'onChange', null, function(e) { dax_saveInput(editor.getValue(),contentId); } );
            dojo.connect(editor, 'onBlur',   null, function(e) { dax_saveInput(editor.getValue(),contentId); } );
        }

        //  Connect Scrubber
        dojo.connect(editor, 'onKeyPress', null, function(e) { if ( e.keyChar == 'v' && ((! dojo.isMac && e.ctrlKey) || (dojo.isMac && e.metaKey)) ) {
            clearTimeout(dax_scrubTimer);  dax_scrubTimer = setTimeout( function () {
                editor.setValue( dax_scrub( editor.getValue(), dax_scrub_config ) );
            }, 100);
        } });

        // Select all the text once the editor is built
        loadWait = 0;  loadWait = function() {
            if ( ! editor.isLoaded ) { setTimeout(loadWait, 10); }
            //  I don't know why, but in IE, we need to do yet another setTimeout and then it will actually work...
            else {
                setTimeout(function() {
                    var ed = editor;
 
                    // Trigger Stylesheets
                    for (var i = 0;i < dax_inEditorSimulateClasses.length;i++) { 
                        dojo.addClass(editor.document.body,dax_inEditorSimulateClasses[i]);
                    }
                    

                    editor.execCommand('SelectAll'); 
                    // And toss the Undo step that line just made (doesn't work in IE)
                    editor.endEditing(); 
                    // Once we get to here we're ok with letting
                    // the edits be live (see the below large
                    // comment for context)
                    tmpCurrentDAXModuleSavedContent = '';

                    // Now, Scroll into view, just in case...
                    setTimeout(function () { dijit.scrollIntoView(editor.domNode); }, 300)

                }, 20); 
            }
        };
        setTimeout(loadWait, 10);
    }
    else if (type == 'input') {
        tmpCurrentDAXModuleSavedContent = contentDiv.innerHTML;
        escValue = htmlentities(contentDiv.innerHTML);
        contentDiv.innerHTML = ( '<form onsubmit="return false">'
                                 +'<input id="dax_editable_content_input-'+ contentId +'" type="text"'
                                 +        ( editable_style ? '       style="'+ htmlentities(editable_style) +'"' : '' )
                                 +'       onChange="dax_saveInput(this.value,'+"'"+ contentId +"'"+')"'
                                 +'       onBlur="dax_saveInput(this.value,'+"'"+ contentId +"'"+')"'
                                 +'       value="'+ escValue +'"'
                                 +'>'
                                 +'</form>'
                               );

        theInput = dojo.byId("dax_editable_content_input-"+ contentId);
        theInput.focus();
        theInput.select();
    }
    else if (type == 'textarea') {
        tmpCurrentDAXModuleSavedContent = contentDiv.innerHTML;
        escValue = contentDiv.innerHTML.replace(/\<\/textarea[^\>]*\>/igm, "&lt;/textarea&gt;").replace(/\<br[^\>]*\>/igm, "\n");
        contentDiv.innerHTML = ( '<form onsubmit="return false">'
                                 +'<textarea id="dax_editable_content_textarea-'+ contentId +'"'
                                 +        ( editable_style ? '       style="'+ htmlentities(editable_style) +'"' : '' )
                                 +'       onChange="dax_saveInput(this.value,'+"'"+ contentId +"'"+')"'
                                 +'       onBlur="dax_saveInput(this.value,'+"'"+ contentId +"'"+')"'
                                 +'>'
                                 +escValue
                                 +'</textarea>'
                                 +'</form>'
                               );

        theInput = dojo.byId("dax_editable_content_textarea-"+ contentId);
        theInput.focus();
        theInput.select();
    }

    //  In some circumstances, someone double, triple or more
    //  clicking on a richtext editor will cause it to quickly
    //  open, then close a module.  In some browsers this is
    //  harmless, but in IE it usually hasn't loaded the editor
    //  content yet and thus when getValue() is called to go and
    //  save the DAX module the content is empty.  Thus, too many
    //  quick clicks will end up actually emptying the value.  So
    //  the workaround is that if the box is closed within 2
    //  seconds of the open call, then dax_saveInput will just
    //  ignore the passed in value and use this temp variable.
    //  Here we are setting a 2 second delay to clear out the
    //  variable...
    setTimeout(function () { tmpCurrentDAXModuleSavedContent = ''; }, 2000 );

    return false;
}

function dax_findAndCloseAllFocusedContentSections() {
    dojo.forEach(dojo.query('.dax_editable-current_focus'), function(elm,i) {
        //  Get the contentId
        contentId = ( elm.id.match(/^dax_editable-([\w\-]+)$/) )[1];

        //  Try for an input type
        inputElm = dojo.byId('dax_editable_content_input-'+ contentId);
        if ( inputElm ) {
            dax_saveInput(inputElm.value, contentId);
            return true;
        }
        inputElm = dojo.byId('dax_editable_content_textarea-'+ contentId);
        if ( inputElm ) {
            dax_saveInput(inputElm.value, contentId);
            return true;
        }
        inputElm = dijit.byId('dax_editable_content_editor-'+ contentId);
        if ( inputElm ) {
            dax_saveInput(inputElm.getValue(), contentId);
            return true;
        }
    });
}


var raceConditionFlags = [];
function dax_saveInput(theValue, contentId) {
    // Race Condition Stopper (becuase both the onBlur and onChange fire at the same time)
    if ( raceConditionFlags[contentId] != 0 ) return; 
    myNum = raceConditionFlags[contentId] = ++raceConditionFlags[contentId];
    if (myNum != raceConditionFlags[contentId]) return;

    // If the tmpCurrentDAXModuleSavedContent var is not empty
    // then use it instead.
    //
    //   NOTE: see the above comment at the end ofdax_editContent
    //   for reasons why this is...
    if ( tmpCurrentDAXModuleSavedContent.length > 0 ) {
        console.log('Reverting DAX Module content.  (Module was closed 2 too quickly to have been edited!)');
        theValue = tmpCurrentDAXModuleSavedContent;
    }


    // Delay this all in a 10th of a second, so we don't screw with the onClick of other content modules
    setTimeout(function() {
        rootDiv = dojo.byId("dax_editable-"+ contentId);

        //  Kill all close buttons
        dojo.query('.hackDojoEditorCloseLink').forEach(dojo.destroy);

        //  If the content type was textarea, then do some scrubbing and translate newlines to BR's
        if ( dojo.hasClass(rootDiv, 'dax_editable-textarea') ) {
            theValue = theValue.replace(/\<br[^\>]*\>/igm, "\n").replace(/\n\n+/igm, "\n\n").replace(/\n/igm, "<br/>");
        }
        //  Else if the content type was richtext, then do further scrubbing
        if ( dojo.hasClass(rootDiv, 'dax_editable-richtext') ) {

            //  Do a full DAX scrub now...
            theValue = dax_scrub( theValue, dax_scrub_config );

//            // Undo the style hacking we did above...
//            newHtml = theValue.replace(/^\s*<style>[^\<]+<\/style>\s*<div class="main-content">\s*/,'');
//            if ( newHtml != theValue ) newHtml = newHtml.replace(/\s*<\/div>\s*$/,'');
//            theValue = newHtml;
            // Whack off space at the beginning and end
            theValue = theValue.replace(/^(\s*\<br[^\>]*\>|\s*\<p[^\>]*\>\s*\<br[^\>]*\>\s*\<\/p[^\>]*\>)+/ig, "").replace(/(\s*\<br[^\>]*\>|\s*\<p[^\>]*\>\s*\<br[^\>]*\>\s*\<\/p[^\>]*\>)+$/ig, "").replace(/^\s+/ig, "").replace(/\s+$/ig, "");
        }

        /// For scrubbing by whoever...
        theValue = (dax_saveInput_scrub)(theValue, contentId);

        // Put back the "click here" text if it's empty...
        valueToSave = theValue;
        if ( theValue == '' ) theValue = '<i>Click here to add Content</i>';

        // Destroy the widget if it's there
        widgetToDestroy = dijit.byId('dax_editable_content_editor-'+ contentId);
        if ( widgetToDestroy ) widgetToDestroy.destroy();

        // Reset the content back to just plain text
        dojo.byId("dax_editable_content-"+ contentId).innerHTML = theValue;
        // Not in edit mode any more
        dojo.removeClass(rootDiv,'dax_editable-current_focus');

        // Now, send the AJAX submit to save the value
        dax_doSaveInput(valueToSave, contentId);

        //  Reset the raceCondition flag
        raceConditionFlags[contentId] = 0;
    }, 100 );

    setTimeout(function() {
        //  Reset it after 200ms regardless
        //    (Note, his is Only to stop the double-submit's that are send instantaneously...  And even then it's just to reduce a little load...)
        raceConditionFlags[contentId] = 0;
    }, 200 );
}


//  A stub to be overridden
var dax_saveInput_scrub = function(valueToSave, contentId) { return valueToSave; };


function dax_doSaveInput(valueToSave, contentId) {
    // Now, send the AJAX submit to save the value
//    console.log(DAX_BASE +'/save_content_section.php');
//    ajax_submit(DAX_BASE +'/save_content_section.php', { content_id: contentId, content: valueToSave }, '', 'hash' );

    //  Raw Dojo XHR
    var deferred = dojo.xhrPost({
        url: DAX_BASE +'/save_content_section.php',
        content: { content_id: contentId, content: valueToSave },
        preventCache: true,
        handleAs: "text",
        error: function(error) {
            alert("Your last change was not saved due to an error:"+ error);
        },
        timeout: 300000,
        load: function() {}
    });
}



function htmlentities(str) {
    return str.replace(/\<\/textarea[^\>]*\>/igm, "&lt;/textarea&gt;").replace(/\<br[^\>]*\>/igm, "\n").replace(/\&/igm, "&amp;").replace(/\"/igm, "&quot;");
}


/////////////////////////
///  Image Edit Modules

var imagesFileMask = ["Images", "*.jpg;*.jpeg;*.gif;*.png"];
//  COnnect Events to the uploadImage 
dojo.addOnLoad(function() {
    var imageContIdToWidgetMap = {};
    dojo.query('.uploaderInsideNode').forEach(function(elm){
        
        widgetid = dojo.attr(elm.parentNode,'widgetid');
        uploader = dijit.byId(widgetid);
        imageContIdToWidgetMap[widgetid] = dojo.attr(elm.parentNode,'widgetid').replace(/^dax_editable_content-/,'');

        //  Set some programmatic Settings
        uploader.fileMask = imagesFileMask;

        uploader.throwDAXError = dojo.hitch(uploader, function() {
            if ( ! this.dontThrowDAXError ) {
                alert("There was an error uploading your image.  The Image should be of type, JPG, JPEG, GIF, or PNG.\n\nSometimes this is caused when the image file is corrupted, or too large ( >4 MB ).\n\nPlease try again.");
                
                // Manually Reset stuff
	        this.fileList = [];
	        this._resetHTML();
	        this.set("disabled", false);
                //  Our own Measures...
                this.insideNode.style.left = '';
                this.progNode.style.display = 'none';
                this.progNodeDAXPrepped = false;
                //  Stop Other Events from Firing
                clearTimeout(this.customDAXTimeout);
                this.customDAXTimeout = false;
                this.dontThrowDAXError = true;
                setTimeout(dojo.hitch(this,function() { this.dontThrowDAXError = false; } ), 5000);
            }
        });

//////  Debugging
///          console.log(contentId);
///          console.log(uploader);
///          dojo.connect(uploader, "onLoad", function(dataArray) {
///              console.log("LOADED!!!");
///          });
///          dojo.connect(uploader, "onCancel", function(dataArray) {
///              debugger;
///          });
        dojo.connect(uploader, "onProgress", function(dataArray){
            // Manually re-style the Progress Bar
            if ( ! this.progNodeDAXPrepped ) {
                //  Customize the progress bar a little further on Completion
                dojo.query('a',this.progNode).forEach(function(node, index, arr){
                    node.innerHTML = 'Loading...';
                    dojo.style( node, { color: '#000',
                                        textDecoration: 'none'
                                      }
                              );
                    dojo.style( node.parentNode, { paddingLeft: '3px',
                                                   width: '100px',
                                                   textAlign: 'center',
                                                   border: '1px solid black'
                                                 });
                });
                dojo.style( this.progNode, { paddingLeft: '3px',
                                             width: '100px',
                                             backgroundColor: '#f00',
                                             backgroundImage: '',
                                             top: '18px',
                                             left: '-42px',
                                             height: '18px',
                                             display: ''
                                           });
                this.fhtml.nr.w = '100'; // Actually changing the Flash HTML width, but it seems to be OK...

                this.progNodeDAXPrepped = true;
                // Reset this...
                this.dontThrowDAXError = false;
            }

            if ( dataArray[0].percent == 100 ) {
                //  Customize the progress bar a little further on Completion
                dojo.query('a',this.progNode).forEach(function(node, index, arr){
                    node.innerHTML = 'Processing...';
                });

                // Do our own Timeout in case the Dojo one craps out..
                if ( ! this.customDAXTimeout ) {
                    this.customDAXTimeout = setTimeout(dojo.hitch(this,function() { this.throwDAXError(); }), this.serverTimeout );
                }
            }
        });

        dojo.connect(uploader, "onError", function(dataArray) { this.throwDAXError(); });
        dojo.connect(uploader, "onComplete", function(dataArray){
            //  Check out what we got back
            if ( dataArray[0].file == '' ) { this.throwDAXError(); }
            else {
                //  Success, CALL OFF THE DOGS!!
                clearTimeout(this.customDAXTimeout);
                this.customDAXTimeout = false;
                this.dontThrowDAXError = true;
                setTimeout(dojo.hitch(this,function() { this.dontThrowDAXError = false; } ), 5000);
                
                contentId = imageContIdToWidgetMap[ this.id ];

                //  Save the value
                dax_doSaveInput(dataArray[0].file, contentId);
                //  Update the image
                dojo.byId('dax_editable-image-'+ contentId).src = dataArray[0].file;

                this.progNodeDAXPrepped = false;
            }
        });
    });
});


var applyDojoHacksAlreadyDone = false;
function applyDojoHacks() {
    if ( ! applyDojoHacksAlreadyDone ) {
        //  Allow some new FontChoice Options
        dijit._editor.plugins._FormatBlockDropDown.prototype.values = ["noFormat", "p", "h1", "h2", "h3", "pre", 'img class="float_none"', 'img class="float_left"', 'img class="float_right"'];
        dijit._editor.nls.FontChoice.en_us['img class="float_none"'] = 'Flow Images Normally';
        dijit._editor.nls.FontChoice.en_us['img class="float_right"'] = 'Align Images Right';
        dijit._editor.nls.FontChoice.en_us['img class="float_left"']  = 'Align Images Left';

        //  Override the _FormatBlockDropDown's _execCommand method to allow Image Aligning
        var _execCommand_dist = dijit._editor.plugins._FormatBlockDropDown.prototype._execCommand;
        dijit._editor.plugins._FormatBlockDropDown.prototype._execCommand = function (editor, command, choice) {
            var runSuper = dojo.hitch(this, "_execCommand_dist");

            // summary:
            //          Over-ride for default exec-command label.
            //          Allows us to treat 'none' as special.
            var m = choice.match(/^([a-z0-9_]+)\s+class=\"([^\"]+)\"\s*$/i);
            if ( dojo.isArray(m) ) {
                var theTag = m[1].toLowerCase();
                var theClass = m[2];
                var start;
                var end;
                var sel = dijit.range.getSelection(editor.window);
                if(sel && sel.rangeCount > 0){
                    var range = sel.getRangeAt(0);
                    var node, tag;
                    if(range){
                        start = range.startContainer;
                        end = range.endContainer;

                        // find containing nodes of start/end.
                        while ( start
                                && start !== editor.editNode
                                && start !== editor.document.body
                                && start.nodeType !== 1
                              ) {
                            start = start.parentNode;
                        }
                        while ( end
                                && end !== editor.editNode
                                && end !== editor.document.body
                                && end.nodeType !== 1
                              ) {
                            end = end.parentNode;
                        }

                        // Find all tags of this type...
			node = start;
			while(dojo.withGlobal(editor.window, "inSelection", dijit._editor.selection, [node])){
                            // Query for all tags...
                            dojo.forEach(dojo.query(theTag, node), function(elm) {
                                dojo.removeAttr(elm,'class');
                                dojo.addClass(elm,theClass);
                            });

			    node = node.nextSibling;
			}
                        
                        editor.onDisplayChanged();
                    }
                }
            }else{
                runSuper(editor, command, choice);
            }
        };
        dijit._editor.plugins._FormatBlockDropDown.prototype._execCommand_dist = _execCommand_dist;

        // Don't run twice or we get infinite loops!
        applyDojoHacksAlreadyDone = true;
    }
}

