<?php
function video_clean(){	
        include(get_template_directory() .'/inc/core/components/video/video-model.php');
        if (!empty($video)) {?>
                <div id="video" class="container-fluid hidden-xs pdtb-default">
                        <div class="container">
                                <div class="row">
                                        <div class="col-md-5 video-heading">
                                                <h4><?php echo $video['video_cat']; ?></h4>
                                                <div class="h5">
													<?php echo $video['video_heading']; ?>
												</div>
                                                <a href ="/videos"><button type="button" class="btn btn-default btn-lg">Zum Video Portal</button></a>
                                        </div>
                                        <div class="col-md-7 video">

                                                <iframe 
                                                        class="video" 
                                                        width="500" 
                                                        height="265" 
                                                        loading="lazy" src="https://www.youtube.com/embed/<?php echo $video['video']; ?>" 
                                                        title="<?php echo $video['video_heading']; ?> Player" 
                                                        frameborder="0" 
                                                        allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" 
                                                        allowfullscreen>
                                                </iframe>
                                        </div>	
                                        <!--<div class="col-md-12">
                                                        <?php //echo $video['video_text']; ?>
                                        </div>-->
                                </div>	

                        </div>
                </div>	
        <?php
                }
        }

function videos_all_view(){
	include(get_template_directory() .'/inc/core/components/video/video-model.php');
	foreach (videos_all() as $video) {?>
		<div class="container-fluid video-container mrt20 mrb20" id="video_container-<?php //echo $index; ?>">
			<div class="container">
				<div class="row">
					<div class="col-md-5 video-heading">
						<h4><?php echo $video['video_cat']; ?></h4>
						<h5><?php echo $video['video_heading']; ?></h5>
						<a href =""><button type="button" class="btn btn-default btn-lg">zu ....</button></a>
					</div>
					<div class="col-md-7 video">
						<iframe 
								class="video" 
								width="500" 
								height="265" 
								loading="lazy" src="https://www.youtube.com/embed/<?php echo $video['video']; ?>" 
								title="<?php echo $video['video_heading']; ?> Player" 
								frameborder="0" 
								allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" 
								allowfullscreen>
						</iframe>
					</div>	
					<div class="col-md-12">
						<?php //echo $video['video_text']; ?>
					</div>
				</div>
			</div>
		</div>	
	<?php } 
}	