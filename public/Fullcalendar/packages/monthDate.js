var date = new Date();

var month = date.getMonth()+1;
var day = date.getDate();

var actualDate = date.getFullYear() + '/' +
  ((''+month).length<2 ? '0' : '') + month
$('#actualDate').val(actualDate);