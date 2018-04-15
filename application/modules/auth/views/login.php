
<h1 class="text-center">Enter Your Email and Password</h1>
<div class="errors col-md-4 offset-md-4">
<?php echo validation_errors() ?>
</div>

<?php echo form_open_multipart('auth/login') ?>
	<div class="row">
		<div class="col-md-4 offset-md-4">
			<div class="form-group">
				<label for="email">Email</label>
				<input type="email" class="form-control" name="email" placeholder="Email">
			</div>
			<div class="form-group">
				<label for="body">Password</label>
				<input type="password" class="form-control" name="password" placeholder="Password">
			</div>
			<button type="submit" class="btn btn-success btn-block">LOGIN</button>
		</div>
	</div>
</form>

