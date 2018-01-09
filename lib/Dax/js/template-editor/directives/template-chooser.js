angular.module("cmsEditApp")
.directive('templateChooser', [function () {
	var appBase = angular.module('cmsEditApp').appBase;

	var default_scope = {
		local : '=templateData',
		positionControlsArray : '=',
		suppressContentTile : '=',
		overrideOptions : '=',
	};

	function generateGuid() {
		return 'xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx'.replace(/[xy]/g, function(c) {
			var r = Math.random()*16|0, v = c == 'x' ? r : (r&0x3|0x8);
			return v.toString(16);
		});
	};

	function link(scope, element, attrs) {
		///  Template Rename Map
		if ( typeof scope.local != 'undefined'
			&& typeof scope.local.template != 'undefined'
			&& typeof window.cms_template_rename_map[ scope.local.template ] != 'undefined'
			) {
			scope.local.template = window.cms_template_rename_map[ scope.local.template ]; // Edits actual JSON model
		}

		scope.this_parent = scope.$parent;
		scope.dax = scope.$parent.dax;
		scope.__template_chooser_uniqId = generateGuid();
		scope.$watch('overrideOptions', function () {
			if(typeof scope.overrideOptions != "undefined"){
				for (var i in scope.overrideOptions) {
					scope[i] = scope.overrideOptions[i];
				}
			}
		},true);
	}
	return {
		restrict: 'E',
		scope: default_scope,
		link: link,
		templateUrl: appBase +'/directives/template-chooser.html',
	};
}]);
