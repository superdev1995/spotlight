function downloadAsPdf() {
	var getUrl = window.location;
	var baseUrl = getUrl .protocol + "//" + getUrl.host + "/";

	var data = '<html><head><link rel="stylesheet" href="' + baseUrl + 'stylesheets/font-awesome.min.css"><link rel="stylesheet" href="' + baseUrl + 'stylesheets/pdf.css"></head><body>' + $('#printable').html() + '</body></html>';

	// Build a form
	var form = $('<form></form>').attr('action', '/export/pdf').attr('method', 'post');
	// Add the one key/value
	form.append($("<input/>").attr('type', 'hidden').attr('name', 'html').attr('value', data));
	//send request
	form.appendTo('body').submit().remove();
}
