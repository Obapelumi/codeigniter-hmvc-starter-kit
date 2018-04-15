<html>
    <head>
		<link rel="stylesheet" type="text/css" href="/assets/css/bootstrap.min.css">
		<link rel="stylesheet" type="text/css" href="/assets/css/style.css">
		<link rel="shortcut icon" type="image/x-icon" href="/assets/img/favicon.ico">
        <title><?php echo $title; ?></title>
    </head>
    <body>
	<nav class="navbar navbar-expand-md navbar-dark bg-dark mb-4">
      <a class="navbar-brand" href="#"><?php echo $title; ?></a>
      <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarCollapse" aria-controls="navbarCollapse" aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
      </button>
      <div class="collapse navbar-collapse" id="navbarCollapse">
        <ul class="navbar-nav mr-auto">
          <li class="nav-item">
						<a class="nav-link" href="/">Home</a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="/about">About</a>
		  		</li>
          <li class="nav-item">
            <a class="nav-link" href="/posts">Blog</a>
		 	 		</li>

					<?php if(session_authenticated()) : ?>
						<li class="nav-item">
							<a class="nav-link" href="/dashboard">Dashboard</a>
						</li>
					<?php endif ?>
					
					<?php if(session_authenticated()) : ?>
						<li class="nav-item">
							<a class="nav-link" href="/create-post">Create Post</a>
						</li>
					<?php endif ?>

					<?php if(!session_authenticated()) : ?>
						<li class="nav-item">
							<a class="nav-link" href="/auth/login">Login</a>
						</li>
					<?php endif ?>

					<?php if(!session_authenticated()) : ?>
						<li class="nav-item">
							<a class="nav-link" href="/auth/register">Register</a>
						</li>
					<?php endif ?>
        </ul>
				<?php if(session_authenticated()) : ?>
					<?= form_open('auth/logout') ?>
						<input type="hidden" name="logout" value="logout">
						<button class="btn btn-danger" type="submit">LOGOUT</button>
					</form>
				<?php endif ?>
      </div>
    </nav>
	<div id="container" class="container">
		<div id="body">
