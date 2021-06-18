<?php
$ip = $_SERVER['REMOTE_ADDR'];
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>Untitled Document</title>
<style type="text/css">
body,td,th {
	font-family: Tahoma, Geneva, sans-serif;
	font-size: 16px;
	color: #000;
}
body {
	background-color: #CCC;
}
</style>

<script language="javascript">
function fLoadResults(platform) {
	var domain = document.getElementById('domain').value;
	domain= encodeURIComponent(domain);
	var keywords = document.getElementById('keywords').value;
	keywords= encodeURIComponent(keywords);
	var ip = document.getElementById('ip').value;
	var trace = document.getElementById('trace').value;
	var redirect = "http://www.toptrafficsource.com/dc/"+ platform + ".php?domain="+domain+"&keywords="+keywords+"&ip="+ip+"&trace="+trace;
	//alert (redirect);
	parent.content.location.href = redirect; 
}
</script>
</head>

<body>
	<form>
		<table>
			<tr>
				<td width="225">
					<label>Domain
						<input type="text" name="domain" id="domain" />
					</label>
				</td>
				<td width="238">
					<label>Keywords
						<input type="text" name="keywords" id="keywords" />
					</label>
				</td>
				<td width="180"><label>IP
					<input type="text" name="ip" id="ip" value="<?=$ip?>"/>
				</label></td>
				<td width="85"><label>Trace
				<select name="trace" id="trace" >
					<option>0</option>
					<option selected="selected">1</option>
					<option>2</option>
					<option>3</option>
				</select>
				</td>
				<td>
					<input type="submit" name="zc" id="zc" value="ZeroClick™" onclick="fLoadResults('zc')" />
					<input type="submit" name="dc" id="dc" value="DirectClick™" onclick="fLoadResults('dc')"/>
					<input type="reset" name="Reset" id="reset" value="Clear Fields" />
				</td>
			</tr>
		</table>
	</form>
</body>
</html>
