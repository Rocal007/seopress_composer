<?php
function listen_clean(){	
    include(get_template_directory() .'/inc/core/components/listen/listen-model.php');
    if (isset($listen)){?>
		<div class="container-fluid listen pdtb-default" id="listen">
			<div class="container listen-container">
				<h4 class="listen-heading text-center"><?php echo $listen[0]['listen_heading'];?></h4>
				<?php 
					foreach ($listen[0]["listen_block"] as $liste) {?>
						<div class="col-md-4 liste">

							<div class="listen-block-heading">
								<?php echo $liste->uberschrift_block;?> 
							</div>

							<ul>
								<?php 
								foreach ($liste->leistung as $leistungen_single) {
									$leistung_info = apply_filters( 'the_content', $leistungen_single->info_single);	
									$leistung_info = wp_strip_all_tags($leistung_info);?>
									<li data-toggle="tooltip" data-placement="top" title="<?php echo $leistung_info;?>">
										<?php echo $leistungen_single->leistung_single;?>
									</li>
								<?php } ?>
							</ul>
						</div>
						<?php } ?>
			</div>	
		</div>
		<?php } 
}