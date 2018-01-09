'use strict';
/*
	install:  add iDropzone to app config

	example:

	var app = angular.module('myApp', [
		'iDropzone'
	])

	config:  Place the following in app.js

	angular.module('myApp').constant("iDropzoneConfig", {
		uploadPath:"/upload-pending-file/",
		templatePath:"/views/templates/idropzone.html",
		acceptedFileTypes: {
			"image": [
				"image/jpeg",
				"image/gif",
				"image/pjpeg",
				"image/png",
				"image/svg+xml"
			],
			"audio": [
				"audio/mp3",
				"audio/mpeg",
				"audio/mp4",
				"audio/ogg",
				"audio/vorbis",
				"audio/vnd.wave"
			],
			"other": [
				"application/pdf",
				"application/zip",
				"application/gzip",
				"application/vnd.openxmlformats-officedocument.wordprocessingml.document",
				"application/msword"
			]
		}

	});

	template content:

		<div>
			<div ng-switch="showExisting()">

				<div ng-switch-when="true">

					<div style="position: relative;" ng-click="editFile()">
						<img ng-src="{{data.existing.img}}" style="width:100%" />
						<span  style="position: absolute;top: 10px;right: 10px;">Edit</span>
					</div>
				</div>
				<div ng-switch-when="false">
					<div class="dropzone " ng-class="DropzoneClass()" style="margin: 0px auto;">
						<div ng-repeat="pre in preview track by $index">
							<input type="hidden" name="{{data.identifier}}-pending-file-id[]" value="{{pre.pendingfileid}}"/>
						</div>
					</div>
					<div ng-show="data.old"><a ng-Click="cancelEdit()">Cancel Edit</a></div>
				</div>
			</div>
		</div>


	use: 	<iDropzone json='{"acceptFileTypes":"image","maxFiles":6,"maxFileSize":15,"existing": {"img":"sample.jpg"} }' ></iDropzone>
	existing is not required.

 */


angular.module('iDropzone',[])
.directive('idropzone', [ 'iDropzoneConfig', function(iDropzoneConfig){ 
	return{
		restrict:'EA',
		replace:true,
		scope: {
			uploadUrl: '=',
			ngModel: '=',
	    },
		//template:'<div>iDropzone1232</div>',
		templateUrl: iDropzoneConfig.templatePath,
		link:function(scope,element,attrs){
			scope.data = angular.fromJson(attrs.json);
			$(element).show();

			///  When nothing is going on, reset back to normal mode
			var resetState = function(dropzone) {
				if(typeof scope.ngModel != 'undefined' && scope.ngModel.length > 3){
					scope.existing = {'img':scope.ngModel};
				}
				else { scope.existing = false; }
//				$(element).find('.dz-preview').remove();
				if ( typeof dropzone != 'undefined' && typeof dropzone.files != 'undefined' && dropzone.files.length > 0 ) {
					dropzone.removeAllFiles();
				}
			};
			resetState();

			scope.hasError = 'false';
			scope.errorType = '';


			///  Immediately uploadable
			var hideOutlineTimeout = null;
			scope.dropzone = element.find('.dropzone').dropzone({
				url: scope.uploadUrl,
				autoProcessQueue:true,
				maxFiles: scope.data.maxFiles,
				maxFilesize: scope.data.maxFileSize,
				accept: function(file,done){
					if(iDropzoneConfig.acceptedFileTypes[scope.data.acceptFileTypes].indexOf(file.type) == -1){
						done('Unacepted File format.');
					}else{
						done();
					}
				},
				init: function() {

					//List of watchable events
					//this.on("drop", function(event)      { console.log('drop'); scope.$apply(); });
					//this.on("dragstart", function(event) { console.log('dragstart'); });
					//this.on("dragend", function(event)   { console.log('dragend'); });
					//this.on("dragenter", function(event) { console.log('dragenter'); });
					this.on("dragover", function(event)  {
						console.log('dragover');
						if ( hideOutlineTimeout ) clearTimeout(hideOutlineTimeout);
						scope.existing = false;
						scope.$apply();
					});
					this.on("dragleave", function(event) {
						console.log('dragleave'); 
						if ( hideOutlineTimeout ) clearTimeout(hideOutlineTimeout);
						hideOutlineTimeout = setTimeout(function() {
							resetState(this);
							scope.$apply();
						},50);
					});
					//this.on('addedfile', function(file) {});
					//this.on("removedfile", function(file) {	});
					//this.on("selectedfiles", function(file) { alert(file); });
					//this.on("thumbnail", function(file,thumbnail) {});
					this.on("error", function(file,message) {
						///  Reset look to un-uploaded again...
						resetState(this);
						alert(message);
						scope.$apply();
					});
					this.on("processing", function(file) { 
						this.options.url = scope.uploadUrl;
					});
					//this.on("uploadprogress", function(file) { alert(file); });
					//this.on("sending", function(file) {});
					//this.on("success", function(file) {});
					this.on("complete", function(file) {
						if(file.status == "success"){
							var imageTypes = iDropzoneConfig.acceptedFileTypes[scope.data.acceptFileTypes];
							if(imageTypes.indexOf(file.type) == -1){
								resetState(this);
								alert('Invalid Type of File.  Please Try Again.');
							}
							else {
								///  Parse response
								var jsonResponse = null;
								try {
						            jsonResponse = angular.fromJson(file.xhr.response); // verify that json is valid
						        }
						        catch (e) {
						        	resetState(this);
						        	alert('Error Uploading.  Developer: please check console...'+"\n\n"+file.xhr.response.substr(0,500));
						        }

						        if ( jsonResponse !== null ) {
						        	scope.ngModel = jsonResponse.file;						        	
						        	resetState(this);
						        }
						    }
							scope.$apply();
						}
						else {
							resetState(this);
//							alert('Error Uploading...');
						}
					});
				  	//this.on("canceled", function(file) { alert(file); });
				  	//this.on("maxfilesreached", function(file) {	});
				  	//this.on("maxfilesexceeded", function(file) { });
					//this.on("processingmultiple", function(fileList) { alert(fileList); });
					//this.on("sendingmultiple", function(fileList) { alert(fileList); });
					//this.on("successmultiple", function(fileList) { alert(fileList); });
					//this.on("completemultiple", function(fileList) { alert(fileList); });
					//this.on("canceledmultiple", function(fileList) { alert(fileList); });
					//this.on("totaluploadprogress", function(fileList) { console.log(fileList); });
					//this.on("reset", function(fileList) {  });

				},
				//previewTemplate: "<div style='display:none'></div>",
				//createImageThumbnails:true,
				//addRemoveLinks:true

			});

			scope.showDropzoneOutline = function(){
				return (
					typeof scope.existing == 'undefined'
					|| scope.existing === false
					|| typeof scope.existing.img == 'undefined'
					|| scope.existing.img.toString().length < 3
					);
			};
		}
	}


}]);

