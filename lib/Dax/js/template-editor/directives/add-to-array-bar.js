angular.module("cmsEditApp")
.directive('addToArrayBar', [function () {
	var appBase = angular.module('cmsEditApp').appBase;

	var default_scope = {
		positionControlsArray : '=positionControlsArray',
		barLabel : '@label',
		size : '@',
		overrideTemplate: '@'
	};

	function link(scope, element, attrs) {
		scope.this_parent = scope.$parent;
		scope.dax = scope.$parent.dax;

		if ( ! scope.barLabel ) {
			scope.barLabel = 'Add a Section';
		}
	}
	return {
		restrict: 'E',
		scope: default_scope,
		link: link,
		templateUrl: appBase +'/directives/add-to-array-bar.html',
	};
}]);
