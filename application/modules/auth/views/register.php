<h1 class="text-center">Fill Out This Awesome Form</h1>
<div class="errors col-md-4 offset-md-4">
<?php echo validation_errors() ?>
</div>

<?php echo form_open_multipart('auth/register') ?>
	<div class="row">
		<div class="col-md-4 offset-md-4">
			<div class="form-group">
				<label for="email">Name</label>
				<input type="text" class="form-control" name="name" placeholder="Name">
			</div>
			<div class="form-group">
				<label for="email">Email</label>
				<input type="email" class="form-control" name="email" placeholder="Email">
			</div>
			<div class="form-group">
				<label for="body">Password</label>
				<input type="password" class="form-control" name="password" placeholder="Password">
			</div>
			<div class="form-group">
				<label for="body">Confirm Password</label>
				<input type="password" class="form-control" name="confirm_password" placeholder="Conffirm Password">
			</div>
			<button type="submit" class="btn btn-success btn-block">REGISTER</button>
		</div>
	</div>
</form>
