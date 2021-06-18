	<table>
		<tr>
		<td>
			<img src="templates/<?=$TEMPLATE?>/images/dc_login.gif" alt="by NetSphere, Inc." width="351" height="176" align="right" />
			<form id="login" name="login" method="post" action="index<?=$DEV?>.php">
				<p>
					<input name="email" type="text" id="email" size="20" maxlength="32" /> Email
				</p>
				<p>
					<input name="password" type="password" id="password" size="20" maxlength="32" /> Password
				</p>
				<p>
					<input type="hidden" name="login" id="login" value="1" />
					<input type="submit" name="submit" id="submit" value="Login" /> <?=$error_msg?>
				</p>
			</form>
		</td>
		<td>
			
		</td>
		</tr>
	</table>