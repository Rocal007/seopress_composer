<?php 
function favorite_blogs_clean() {
    include(get_template_directory() .'/inc/core/components/favorite_blogs/favorite_blogs-model.php');?>
     <div class="container-fluid favorite_blogs pdtb-default">
        <div class="container">            
                <h5><strong>Besucher interessierten sich auch für:</strong></h5>
                    
                <?php for($i = 0; $i < 3; ++$i) {?>
                    <div class="favorite_blogs row">
                        <div class="col-md-4">
                            
                            <div class="favorite_blogs-picture">
                                <a href="<?php echo $blogs[$i]["blog_url"];?>">
									<img alt="<?php echo $blogs[$i]['blog_heading']; ?>" src="<?php echo $blogs[$i]['blog_picture'];?>">
								</a>
                            </div>
                        </div>
                        <div class="col-md-8 favorite_blogs-content" style="border-color:<?php echo colors('primary'); ?>">
                            <div class="favorite_blogs-heading text-left">
								<a href="<?php echo $blogs[$i]["blog_url"];?>"><?php echo $blogs[$i]['blog_heading']; ?></a>
                            </div>
                            <div class="favorite_blogs-index">
                                <ul>
                                <?php 
                                    foreach($blogs[$i]['blog_index'] as $blog_index){?>
                                            <li><small><?php echo $blog_index;?></small></li>
                                    <?php }?>
                                </ul>
                            </div>
                            <!--<div class="favorite_blogs-content">
                            <?php echo $blogs[$i]['blog_main_text']; ?>
                                <?php //echo $favorite_blogs_hs[$index]["favorite_blogs_extension"]; ?>
                            </div>-->
                        </div>
                    </div>
                <?php };?>        
        </div>
	</div>
<?php };?> 