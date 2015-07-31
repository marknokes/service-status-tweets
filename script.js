jQuery(document).ready(function($){
	/**
	* Automatically append today's date to href in service status twitter links
	*/
	var today = new Date(),
		dd = today.getDate(),
		mm = today.getMonth()+1, // Jan. is zero
		yyyy = today.getFullYear();
	if(dd<10)
		dd='0'+dd;
	if(mm<10)
		mm='0'+mm;
	var date = yyyy+"-"+mm+"-"+dd;
	$("#service-status li a").each(function(index){
		var _href = $(this).attr('href');
		$(this).attr('href', _href + date);
	});
	// END Automatically append today's date to href in service status twitter links

	/**
	* Get tweets, parse hashtags, and add class "red" to matching id's
	*/
	$.ajax({
		url: "tweets.php",
		type: "POST",
		data: {getem:1},
		dataType: "json",
		success: function(response){
			if ( response.length !== 0 )
			{
				$.each(response, function(index,value){
					$("#"+value).removeClass('green').addClass('red');
				});
			}
		}
	});
});