var zoom = 1;
	var startSelectionDot = {}
	$(function(){
		$('.linkEditor').change(updateSelectedLink);
		$('.linkEditor').keyup(updateSelectedLink);
		
		$(document).ready( function(){
			var imgURL = getQueryVariable('url');
			if(imgURL!=''){
				$('#canv').remove(); // remove canvas if exists
				$('.loader').eq(0).show(); // add loader
			
				var newImg = document.createElement('img');
				$(newImg).attr({
					'src': imgURL
					}).load(function(){
						$('#step2').show();
						
						var canvas = document.createElement('div');
						$(canvas).attr('id','canv').appendTo('#canvasContatiner').css({
							'width': newImg.width,
							'height': newImg.height,
							'background': 'url('+imgURL+') 0 0 no-repeat'
						});
						$.data(canvas,'realSize',{'width':newImg.width,'height':newImg.height});
						$.data(canvas,'url', imgURL);
						assignCanvas();
//						$('.loader').hide(); // hide loader
					}).error(function(){
//						$('.loader').hide(); // hide loader
						alert('Not a valid image, try a different URL..');
					});
			}else{
				alert('Please Insert a URL..');
			}
		});
		
		$('.stepTitleLink').css({'cursor':'pointer'}).click(function(){
			var which = $(this).index('.stepTitleLink');
			switch (which){
				case 0:
					if(($('.doneMark').eq(0).is(':visible'))&&(confirmRestart())){
						$('.doneMark').eq(1).hide(); // if going from tab 3 to 1
						switchToTab(this);
					}
					break;
				case 1:
					if($('.doneMark').eq(0).is(':visible')){
						switchToTab(this);
					}else{
						alert('You MUST pick an image first.')
					}
					break;
				case 2:
					if($('.doneMark').eq(0).is(':visible')){
						if($('.doneMark').eq(1).is(':visible')){
							switchToTab(this);
						}else{
							alert('If you\'re done adding the links please click the "Finish" button.')
						}
					}else{
						alert('You MUST pick an image first.')
					}
					break;
			}
		})
		
		$('#step2End').click(function(ev){
			if($('.theBox').length > 0){
				$('.loader').eq(1).show(); // add loader
				var thisTime = ev.timeStamp;
				$('.theBox.selected').removeClass('selected');
				$('#linkAdder').slideUp();
				if(validateLinks()){
					$('.loader').eq(1).hide();
					
					var canvas = $('#canv')[0];
					var realSize = $.data(canvas,'realSize');
					var imgURL = $.data(canvas,'url');
					createCodes(imgURL, realSize, ev.timeStamp);
					
//						$('#step2').slideUp(function(){
//							$('#step3').slideDown();
//						});
				}else{
					alert('Some of the areas you selected don\'t have links attached to them.');
					$('.loader').eq(1).hide();
				}
			}else{
				alert('You must create at least one linkage area');
			}
		});
		
		$('.outputCode').click(function(ev){
			this.select();
			ev.preventDefault();
		});
	});
	
	function onBoxSelect(element){
		if($(element).hasClass('highlightedBox')){$(element).removeClass('highlightedBox');}
		
		var linkInfo = $.data(element, 'link');
		if (typeof linkInfo == 'undefined'){
			$.data(element, 'link',{
				'href': '',
				'alt': '',
				'target': '_self'
			})
			linkInfo = $.data(element, 'link');
		}
		
		$('#linkURL').val(linkInfo.href);
		$('#linkAlt').val(linkInfo.alt);
		$('#linkTarget option').each(function(){
			if(($(this).val())==(linkInfo.target)){
				$(this).attr('selected','selected')
			}else{
				$(this).removeAttr('selected');
			}
		});
		
		$('#linkAdder').slideDown();
		$('#linkURL').focus();
	}
	
	function onBoxDelete(element){
		$('#linkAdder').slideUp();
	}
	
	function unSelectBoxes(element, swapped){
		$('.theBox').not('.selected').css({'opacity':0.8});
		if(!swapped){
			$('#linkAdder').slideUp();
		}
	}
	
	function updateSelectedLink(){
		var selectedBox = $('.selected')[0];
		$.data(selectedBox,'link',{
				'href': String($('#linkURL').val()),
				'alt': String($('#linkAlt').val()),
				'target': String($('#linkTarget').val())
		});
	}
	
	function validateLinks(){
		var state = true;
		var currentLink;
		$('#canv').children('.theBox').each(function(){
			currentLink = $.data(this, 'link');
			if(currentLink.href == ''){
				state = false;
				$(this).addClass('highlightedBox');
			}
		});
		return state;
	}
	
	function confirmRestart(){
		var answer = confirm("This will restart the process and delete everthing you've done so far, are you sure?")
		return answer;
	}
	
	function switchToTab(element){
		$(element).next().next().hide();
		$('.steps').not($(element).parent().next()).slideUp();
		$(element).parent().next().slideDown();
	}
	
	function createCodes(url, size, timeStamp){
		var htmlCode = '';
		
		$('.theBox').each(function(){
			var linkInfo = $.data(this,'link');
			var linkPosition = $.data(this,'position');
			htmlCode += '<area shape="rect" coords="'+linkPosition.left+','+linkPosition.top+','+(linkPosition.left+linkPosition.width)+','+(linkPosition.top+linkPosition.height)+'" title="'+linkInfo.alt+'" alt="'+linkInfo.alt+'" href="'+linkInfo.href+'" target="'+linkInfo.target+'">';
		});
		
		window.parent.window.plugin_callback(htmlCode);
		window.parent.$.daxcolorbox.close();
//		$('#HTMLCode').html(htmlCode);
	}

/// Helper Functions

function getQueryVariable(variable) {
    var query = window.location.search.substring(1);
    var vars = query.split('&');
    for (var i = 0; i < vars.length; i++) {
        var pair = vars[i].split('=');
        if (decodeURIComponent(pair[0]) == variable) {
            return decodeURIComponent(pair[1]);
        }
    }
    console.log('Query variable %s not found', variable);
}
