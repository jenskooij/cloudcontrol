<!DOCTYPE html>
<html>
<head>
	<title>Cloud Control CMS</title>
	<link rel="stylesheet" href="<?=\library\cc\Request::$subfolders?>/css/cms.css"/>
</head>
<body class="grid-wrapper login">
	<main class="body grid-container">
		<h1>Cloud Control</h1>
		<section class="login-form grid-box-4">
			<? if (isset($errorMsg)) : ?>
			<div class="errorMsg">
				<p><?=$errorMsg?></p>
			</div>
			<? endif ?>
			<form method="post">
				<div class="form-element">
					<label>Username</label>
					<input required="required" type="text" name="username" placeholder="Username" />
				</div>
				<div class="form-element">
					<label>Password</label>
					<input required="required" type="password" name="password" placeholder="Password" />
				</div>
				<div class="form-element">
					<input class="btn" type="submit" value="Login" />
				</div>
			</form>
		</section>
	</main>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/2.1.4/jquery.min.js"></script>
<script>
$.get('https://api.unsplash.com/photos/random?client_id=e91dda05377e2adf28bdb3bb62ea86366639b73d70eaaab124ceac53919cf60d&category=4', function (result) {
	if (result.urls !== null) {
		$('body').css('background-image', 'url(\'' + result.urls.regular + '\')');
	}
});
</script>

</body>
</html>