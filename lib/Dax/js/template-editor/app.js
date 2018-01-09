'use strict';

//  Use Custom modules
var cmsEditApp__modules = [
	'ngSanitize',
	'iDropzone',
	'textAngular'
	];
if ( typeof window.cms_advanced_editor_custom_angular_modules != 'undefined' ) {
	cmsEditApp__modules = cmsEditApp__modules.concat(window.cms_advanced_editor_custom_angular_modules);
}
angular.module('cmsEditApp', cmsEditApp__modules)
.config(function() {
	angular.module('cmsEditApp').appBase = window.cms_template_editor_base;
});

angular.module('cmsEditApp').constant("iDropzoneConfig", {
	templatePath:"/lib/dax/js/template-editor/lib/dropzone/idropzone.html",
	acceptedFileTypes: {
		"image": [
			"image/jpeg",
			"image/gif",
			"image/pjpeg",
			"image/png",
			"image/svg+xml"
		],
		"video": [
			"video/mp4"
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
