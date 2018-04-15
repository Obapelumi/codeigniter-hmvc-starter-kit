<span style="font-size: 20px;">Categories: </span>
<?php foreach($categories as $category) : ?>
	<a href="<?php echo '/posts/categories/'.$category['slug']; ?>" class="badge badge-primary" style="font-size: 18px; margin-top: 20px;"><?php echo $category['name'] ?></a>
<?php endforeach ?>
<hr>
<?php foreach($posts as $post) : ?>
	<h1><a href="<?php echo '/posts/'.$post['slug']; ?>"><?php echo $post['title'] ?></a></h1>
	<p><?php echo word_limiter($post['body'], 50) ?></p>
	<a href="<?php echo '/posts/'.$post['slug']; ?>" class="btn btn-default">Read More</a>
<?php endforeach ?>

