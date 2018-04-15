
<h1>Start Writing</h1>
<div class="errors">
<?php echo validation_errors() ?>
</div>

<?php echo form_open_multipart('create-post') ?>
  <div class="form-group">
    <label for="title">Title</label>
    <input type="text" class="form-control" name="title" placeholder="Title">
  </div>
  <div class="form-group">
    <label for="body">Body</label>
    <textarea rows="8" class="form-control" name="body"  placeholder="Body"></textarea>
  </div>
	<div class="form-group">
    <label for="body">Category</label>
		<select name="category_id" class="form-control">
			<?php foreach($categories as $category): ?>
				<option value="<?php echo $category['id'] ?>"><?php echo $category['name'] ?></option>
			<?php endforeach ?>
		</select>
  </div>
	<div class="form-group">
		<label for="image">Upload Image</label>
		<input type="file" name="userfile" size="20" class="form-control">
	</div>
  <button type="submit" class="btn btn-success">Submit</button>
</form>

