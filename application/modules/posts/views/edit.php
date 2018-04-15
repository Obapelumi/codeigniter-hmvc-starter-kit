
<h1><?php echo $heading; ?></h1>
<div class="errors">
<?php echo validation_errors() ?>
</div>

<?php echo form_open('edit-post/'. $post['slug']) ?>
  <div class="form-group">
    <label for="title">Title</label>
    <input type="text" class="form-control" name="title" placeholder="Title" value="<?php echo $post['title'] ?>">
  </div>
  <div class="form-group">
    <label for="body">Body</label>
    <textarea rows="8" class="form-control" name="body"  placeholder="Body"><?php echo $post['body'] ?></textarea>
  </div>
	<div class="form-group">
    <label for="body">Category</label>
		<select name="category_id" class="form-control" value="<?php echo $post['category']['name'] ?>">
			<?php foreach($categories as $category): ?>
				<option value="<?php echo $category['id'] ?>"><?php echo $category['name'] ?></option>
			<?php endforeach ?>
		</select>
  </div>
  <button type="submit" class="btn btn-info">Update</button>
</form>

