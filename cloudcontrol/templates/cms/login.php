<!DOCTYPE html>
<html>
<head>
	<title>Cloud Control CMS</title>
	<link rel="stylesheet" href="<?=\library\cc\Request::$subfolders?>css/cms.css"/>
	<link rel="shortcut icon" type="image/png" href="<?=$request::$subfolders?>favicon.ico"/>
</head>
<body class="grid-wrapper login" onload="document.getElementById('username').focus();">

	<main class="body grid-container">
		<h1>Cloud Control</h1>
		<!--[if lt IE 10]>
		<p class="browserupgrade">You are using an <strong>outdated</strong> browser. Please <a href="http://browsehappy.com/">upgrade your browser</a> to improve your experience.</p>
		<![endif]-->
		<section class="login-form grid-box-4">
			<? if (isset($errorMsg)) : ?>
			<div class="errorMsg">
				<p><?=$errorMsg?></p>
			</div>
			<? endif ?>
			<form method="post" onsubmit="document.getElementById('submitButton').className='btn inactive';document.getElementById('loadingSpinner').style.display='inline-block';">
				<div class="form-element">
					<label for="username">Username</label>
					<input id="username" required="required" type="text" name="username" placeholder="Username" />
				</div>
				<div class="form-element">
					<label for="password">Password</label>
					<input id="password" required="required" type="password" name="password" placeholder="Password" />
				</div>
				<div class="form-element">
					<i id="loadingSpinner" class="fa fa-spinner fa-spin fa-fw margin-bottom"></i>
					<input id="submitButton" class="btn" onclick="" type="submit" value="Login" />
					<a href="<?=$request::$subfolders?>" class="btn" title="Return to site"><i class="fa fa-reply"></i></a>
				</div>
			</form>
		</section>
	</main>
<script>
function httpGetAsync(theUrl, callback) {
    "use strict";
    var xmlHttp = new XMLHttpRequest();
    xmlHttp.onreadystatechange = function () {
        if (xmlHttp.readyState === 4 && xmlHttp.status === 200) {
            callback(xmlHttp.responseText);
        }
    };
    xmlHttp.open("GET", theUrl, true); /* true for asynchronous */
    xmlHttp.send(null);
}

(function () {
    "use strict";
    httpGetAsync('https://api.unsplash.com/photos/random?client_id=e91dda05377e2adf28bdb3bb62ea86366639b73d70eaaab124ceac53919cf60d&category=4', function (result) {
        result = JSON.parse(result);
        document.getElementsByTagName('body')[0].style.backgroundImage = 'url(\'' + result.urls.regular + '\')';
    });
}());
</script>
</body>
</html>