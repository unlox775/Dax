'use strict';

/**
 * @ngdoc function
 * @name cmsEditApp.controller:MainCtrl
 * @description
 * # MainCtrl
 * Controller of the cmsEditApp
 */
 angular.module('cmsEditApp')
 .controller('MainCtrl', function ($scope) {
 	$scope.override_header = null;
 	$scope.view_vars = {};

 	$scope.dax = {};

 	$scope.onChangeNavName = function(local) {
		// set related tab_id to SEO-friendly slug
		local.tab_id = local.nav_name.toLowerCase().replace(/[^\w ]+/g,'').replace(/ +/g,'-');
	}

    /// Helper Functions
    $scope.loadScopeKeyFromParent = function(local,keyname){
		// Allows for PHP-modified stuff to initiate scope content
		local[keyname] = window.parent[keyname];
	};
  $scope.dax.loadScopeKeyFromParent = $scope.loadScopeKeyFromParent;
	$scope.saveJSONContent = function(){
		window.cms_edit_output_object.content = angular.toJson($scope.local,true);
		window.cms_edit_output_object.saved_by_cms_editor = true;
	};
	$scope.dax.imageMapperOpen = function(local_data,image_url_property,property,$event) {
		var elm = $event.target;
	    ///  Return, it clicks us again...
	    if ( typeof window.plugin_response != 'undefined' ) {
	    	local_data[property] = window.plugin_response;
	    	window.plugin_response = undefined;
	    }
	    else {
	    	window.plugin_response = undefined;
	    	window.plugin_callback = function(str) {
	    		window.plugin_response = str;
	    		setTimeout(function() {
	    			angular.element(elm).triggerHandler('click');
	    		}, 100);
	    	};
	    	$.daxcolorbox({width: '1000px', height: '800px', top: '10px', iframe: true, href: '/lib/dax/js/template-editor/lib/image-mapper.html?url='+ escape($scope.images_base_url + local_data[image_url_property]) + '&x=foo'});
	    }
	}
	$scope.toggleSkus = function(obj){
		if($('#'+obj))
			console.log("yes");
		else
			console.log("no");
	}
	$scope.dropZoneOpen = function(local_data,image_url_property,property,$event) {
		$.daxcolorbox({width: '1000px', height: '800px', inline: true, href: '#idropzone'});
	}

	var __private = {};
	__private.prototypeNewTemplate = function(local_data){
		if ( typeof local_data == 'undefined' ) return;
		if ( typeof local_data.template == 'undefined' ) return;
		if ( typeof $scope.dax.__templates[local_data.template] == 'undefined' ) return;
		for (var key in $scope.dax.__templates[local_data.template].prototype) {
			if ( typeof local_data[key] == 'undefined' ) {
				if ( typeof $scope.dax.__templates[local_data.template].prototype[key] == 'object'
					|| $scope.dax.__templates[local_data.template].prototype[key] instanceof Array
					) {
					local_data[key] = angular.copy($scope.dax.__templates[local_data.template].prototype[key]);
				}
				else {
					local_data[key] = $scope.dax.__templates[local_data.template].prototype[key];
				}
			}
		}
	};
	$scope.dax.changeTemplate = function(local_data,sub_template){
		local_data.template = sub_template.value;
		__private.prototypeNewTemplate(local_data);
	}
	$scope.dax.addTemplateToArray = function(local_data,subjectArray, useTemplate){
		if ( typeof subjectArray == 'undefined' || ! subjectArray instanceof Array ) { return false; }
		///  USE TO: subjectArray = [];
    var newTemplate = {};
    newTemplate.template = (useTemplate) ? useTemplate : $scope.dax.__templates[local_data.template].default_template;
		__private.prototypeNewTemplate(newTemplate);
		subjectArray.push(newTemplate);
	};
	$scope.dax.removeFromArray = function(subjectArray,remove_me){
		if ( typeof subjectArray == 'undefined' || ! subjectArray instanceof Array ) { return false; }

		for (var i = subjectArray.length - 1; i >= 0; i-- ) {
			if ( subjectArray[i] === remove_me ) {
				subjectArray.splice(i,1);
				return true;
			}
		}
	};
	$scope.dax.moveUpInArray = function(subjectArray,move_me){
		if ( typeof subjectArray == 'undefined' || ! subjectArray instanceof Array ) { return false; }

		for (var i = subjectArray.length - 1; i >= 0; i-- ) {
			if ( subjectArray[i] === move_me ) {
				if ( i == 0 ) return false;
				var tmp = subjectArray.splice(i,1);
				subjectArray.splice(i-1,0,tmp[0]);
				return true;
			}
		}
	};
	$scope.dax.moveDownInArray = function(subjectArray,move_me){
		if ( typeof subjectArray == 'undefined' || ! subjectArray instanceof Array ) { return false; }

		for (var i = subjectArray.length - 1; i >= 0; i-- ) {
			if ( subjectArray[i] === move_me ) {
				if ( i == (subjectArray.length-1) ) return false;
				var tmp = subjectArray.splice(i,1);
				subjectArray.splice(i+1,0,tmp[0]);
				return true;
			}
		}
	};
	$scope.dax.isFirst = function(subjectArray,move_me){
		if ( typeof subjectArray == 'undefined' || ! subjectArray instanceof Array ) { return false; }
		if ( subjectArray.length > 0 && subjectArray[0] === move_me ) { return true; }
		return false;
	};
	$scope.dax.isLast = function(subjectArray,move_me){
		if ( typeof subjectArray == 'undefined' || ! subjectArray instanceof Array ) { return false; }
		if ( subjectArray.length > 0 && subjectArray[subjectArray.length - 1] === move_me ) { return true; }
		return false;
	};
	$scope.mergeObj = function(obj, key, newKey, data){
		obj[key][newKey] = [data];
	};

    /// HACK need to do a 1-element loop so I can do an ng-init

    $scope.toggle = function(element){
    	$scope.view_vars[element] = $scope.view_vars[element] ? false : true;
    	$('#'+element).toggle();
    }

    $scope.onDrop = function(target, data, local, key){
    	var source_info = data.split("|");
    	var source_upc = source_info[0];
    	var source_obj = source_info[1];
    	var validTarget = false;
    	if(source_obj === "upc_list_skus"){
    		var list = $scope.view_vars.upc_list_skus[target].split(",");
    		for(i=0; i<list.length; i++){
    			if(list[i] == source_upc){
    				validTarget = true;
    				break;
    			}
    		}
    		if(validTarget){
    			var str = local[key];
    			str = str.replace(target,source_upc);
    			local[key] = str;
    		}
    	}else if(source_obj === "upc_list"){
    		var str = "";
    		var upcs = local[key].replace(/\n/g,',').replace(/^\s+|\s+$/g,'').replace(',,', ',').replace(/[\s,]+/g, ',').split(/\s*,\s*/);
    		var source_index = null;
    		var target_index = null;

    		for(i=0; i<upcs.length; i++){
    			if(upcs[i] == source_upc){
    				source_index = i;
    			}
    			if(upcs[i] == target){
    				target_index = i;
    			}
    		}

    		if(source_index >= 0 && target_index >= 0){
    			upcs.splice(target_index, 0, upcs.splice(source_index, 1)[0]);
    			local[key] = upcs.toLocaleString().replace(/,+$/, '');
    		}

    	}


    };

    $scope.__one = ['1'];

    $scope.dax.__templates = window.cms_edit_template_json;
    $scope.local = window.cms_edit_initial_content;
    $scope.images_base_url = window.cms_edit_images_base_url;

      ///  Now, compute the sub-template lists for each template
      $scope.dax.__sub_templates = {};
      for (var key in $scope.dax.__templates) {
      	if  ( typeof $scope.dax.__templates[key].sub_templates != 'undefined'
      		&& $scope.dax.__templates[key].sub_templates instanceof Array
      		&& typeof $scope.dax.__templates[key].name != 'undefined'
      		) {
      		$scope.dax.__sub_templates[key] = [];
      	for (var i = 0; i < $scope.dax.__templates[key].sub_templates.length; i++ ) {
      		if ( typeof $scope.dax.__templates[$scope.dax.__templates[key].sub_templates[i]] == 'undefined' ) { continue; }
      		$scope.dax.__sub_templates[key].push({
      			'value': $scope.dax.__templates[key].sub_templates[i],
      			'label': $scope.dax.__templates[$scope.dax.__templates[key].sub_templates[i]].name
      		});
      	}
      }
  }
})
//  Helper filter to turn verbose strings into flattened ID-worthy values
.filter('to_id', function() {
	return function(input) {
		return input.replace(/[^a-z0-9-]/ig,'-');
	};
})
//  Helper filter to turn verbose strings into flattened ID-worthy values
.filter('split_by_commas', function() {
	return function(input) {
		if ( input.match(/^\s*$/) ) return [];
		return input.toString().replace(/\n/g,',').replace(/^\s+|\s+$/g,'').replace(',,', ',').replace(/[\s,]+/g, ',').replace(/,+$/, '').split(/\s*,\s*/);
	};
});
