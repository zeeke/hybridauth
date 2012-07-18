$(function() {
	$( "#hybridauth-openid-div" ).dialog({
			autoOpen: false,
			height: 200,
			width: 350,
			modal: true,
			resizable: false,
			title: 'OpenID',
			buttons: {
				"Login": function() {
					$('#hybridauth-openid-form').submit();
				}
				,
				Cancel: function() {
					$(this).dialog( "close" );
				}
			}
	});

	$("#hybridauth-OpenID").click(function() {
		event.preventDefault();
		$( "#hybridauth-openid-div").dialog( "open" );
	});
});