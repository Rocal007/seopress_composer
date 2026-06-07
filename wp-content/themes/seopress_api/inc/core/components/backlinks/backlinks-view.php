<?php function backlinks() {
include(get_template_directory() .'/inc/core/components/backlinks/backlinks-model.php');?>
	<div class="container">
		<div class="row">
			<?php foreach ($backlinks as $backlink){ ?>
			<div class="col-md-4">
				<a href="<?php echo $backlink['link_url']?>"><?php echo $backlink['link_text']?></a>
			</div>
		<?php }?>
		</div>
	</div>	  			  
<?php }