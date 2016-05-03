<section class="dashboard configuration">
	<h2><i class="fa fa-cogs"></i> <a href="<?=\library\cc\Request::$subfolders?><?=$cmsPrefix?>/configuration">Configuration</a> &raquo; Users</h2>
	<nav class="actions">
		<ul>
			<li>
				<a class="btn" href="<?=\library\cc\Request::$subfolders?><?=$cmsPrefix?>/configuration/users" title="Back">Back</a>
			</li>
		</ul>
	</nav>
	<form method="post" class="panel" id="documentTypesForm">
		<div class="form-element">
			<label for="username">Username</label>
			<input required="required" id="username" type="text" name="username" placeholder="Username" value="<?=isset($user) ? $user->username : '' ?>" />
		</div>
		<div class="form-element">
			<label for="password">Password</label>
			<input<?=isset($user) ? '' : ' required="required"' ?> id="password" type="password" name="password" placeholder="Password" />
			<input type="hidden" name="passHash" value="<?=isset($user) ? $user->password : '' ?>" />
			<input type="hidden" name="salt" value="<?=isset($user) ? $user->salt : '' ?>" />
		</div>
		<div class="form-element">
			<label for="rights">Rights</label>
			<select name="rights[]" id="rights" multiple="multiple">
				<option value="documents"<?=isset($user) && in_array('documents', $user->rights) ? ' selected="selected"' : '' ?>>Documents</option>
				<option value="sitemap"<?=isset($user) && in_array('sitemap', $user->rights) ? ' selected="selected"' : '' ?>>Sitemap</option>
				<option value="images"<?=isset($user) && in_array('images', $user->rights) ? ' selected="selected"' : '' ?>>Images</option>
				<option value="files"<?=isset($user) && in_array('files', $user->rights) ? ' selected="selected"' : '' ?>>Files</option>
				<option value="configuration"<?=isset($user) && in_array('configuration', $user->rights) ? ' selected="selected"' : '' ?>>Configuration</option>
			</select>
		</div>
		<div class="form-element">
			<input class="btn" type="submit" value="Save" />
		</div>
	</form>
</section>