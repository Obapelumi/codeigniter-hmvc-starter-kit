<h1> 
	Posted on: <?= date_format(date_create($post['created_at']), 'l M d, Y')  ?>
	<a 
		href="<?= '/posts/categories/'.$post['category']['slug']; ?>" 
		class="badge badge-primary" 
		style="font-size: 18px; margin-top: 20px;">
		<?= $post['category']['name'] ?>
	</a>
</h1>
<p><?php echo  $post['body']  ?></p>
<hr>
<?php if(belongs_to_user($post['user_id'])) : ?>
	<?= form_open('/posts/delete/'. $post['id']) ?>
		<a href="<?php echo '/edit-post/'.$post['slug']; ?>" class="btn btn-info">EDIT</a>
		<button type="submit" class="btn btn-danger">DELETE</button>
	</form>
<?php endif ?>


