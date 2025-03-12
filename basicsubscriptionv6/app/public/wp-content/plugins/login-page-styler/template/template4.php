<style>
body.login {
	background-color: #f1f1f1;
}

#login {
	background-color: #fff;
	box-shadow: 0 1px 3px rgba(0, 0, 0, 0.13);
	padding: 24px;
	border-radius: 4px;
	margin: 50px auto 0;
	max-width: 400px;
}

#login h1 a {
	background-image: url('<?php echo esc_url( get_stylesheet_directory_uri() ); ?>/images/logo.png');
	background-size: contain;
	height: 84px;
	width: 100%;
	margin-bottom: 24px;
	text-indent: -9999px;
	display: block;
}

#login form {
	margin-top: 24px;
}

#login label {
	display: block;
	margin-bottom: 6px;
	font-weight: bold;
}

#login input[type="text"],
#login input[type="password"] {
	display: block;
	width: 100%;
	padding: 12px;
	margin-bottom: 24px;
	font-size: 16px;
	line-height: 1.5;
	color: #333;
	background-color: #fff;
	border: 1px solid #ccc;
	border-radius: 4px;
}

#login input[type="text"]:focus,
#login input[type="password"]:focus {
	border-color: #4a90e2;
	outline: 0;
}

#login input[type="submit"] {
	display: block;
	width: 100%;
	padding: 12px;
	font-size: 16px;
	font-weight: bold;
	color: #fff;
	background-color: #4a90e2;
	border: none;
	border-radius: 4px;
	cursor: pointer;
}

#login input[type="submit"]:hover {
	background-color: #2569b9;
}
</style>
