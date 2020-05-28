$(document).ready(function() {
    //set initial state.
    $(function () {
	$.nette.init();
/*	$("input[name=searchtext]").on("keyup", function(){
		$.nette.ajax({
			type: 'GET',
			url: {link search!},
			data: {
				'value': $(this).val(),
			}
		});
	});*/
	});

/*	$('td.detail').click(function() {
        window.location = $(this).attr('href');
        return false;
    });*/

	$("a.ajax").on("click", function (event) {
	    event.preventDefault();
	    $.get(this.href);
	});

	var moveLeft = 20;
	var moveDown = 10;
	$(function() {
  		$('a#trigger').hover(function(e) {
    		$('div#pop-up').show()
    			.css('top', e.pageY + moveDown)
      			.css('left', e.pageX + moveLeft)
      			.appendTo('body');
  		}, function() {
    		$('div#pop-up').hide();
  		});
	});


    if ($("input:checkbox[name=inactive]").prop("checked")) {
    	$(".off").show();
    } else {
    	$(".off").hide();
	}

    $("input:checkbox[name=inactive]").click(function() {
	    var $this = $(this);
	    // $this will contain a reference to the checkbox   
	    if ($this.is(':checked')) {
	        // the checkbox was checked 
	        $(".off").show();
	    } else {
	        // the checkbox was unchecked
	        $(".off").hide();
  		}
	});
});
