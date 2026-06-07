<?php
function timeline_clean(){
    include(get_template_directory() .'/inc/core/components/timeline/timeline-model.php');
?>
<div class="container-fluid text-left hidden-xs pdtb-default">
    <div class="container timelines-container">
        <div class="text-center">
			<h3 class="timelines-heading">Ablauf <?php echo $timelines_title;?></h3>
        </div>
        <?php
        //AMP Request (Mobile)
        if (amp_is_request()){?>
            <amp-accordion expand-single-section>
                    <?php
                        foreach($timelines as $timeline) {?>
                            <section>
                                <h5><?php echo $timeline["timeline_title"]; ?></h5>
                                <p><?php echo $timelines["timeline_text"]; ?></p>
                            </section>
                    
                    <?php }?>
            </amp-accordion>
        <?php
        //DESKTOP 
        } else {
        ?>
    <div id="timeline">
        <?php
        foreach($timelines as $timeline) {
            $icon = $timeline['timeline_icon'];?>
            <div class="row timeline-movement">
                <div class="timeline-badge"></div>
                <div class="<?php echo $timeline['timeline_offset_class'];?> col-md-6 timeline-item">
                    <div class="row">
                        <div class="col-sm-11">
                            <div class="timeline-panel credits">
                                <div class="timeline-panel-ul">
                                    <div class="<?php echo $timeline['timeline_icon_class'];?> col-md-3"><?php echo get_icon($icon);?></div>
                                    <div class="primary-border-color <?php echo $timeline['timeline_title_class'];?>">
                                        <?php echo $timeline['timeline_title'];?>
                                    </div>
                                    <div class="<?php echo $timeline['timeline_text_class'];?>"><?php echo $timeline['timeline_text'];?></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <?php }?>
        </div>
      <?php }?>
    </div>
</div>
<?php } ?>

<?php
function timeline_row(){
    include(get_template_directory() .'/inc/core/components/timeline/timeline-model.php');?>
			<div class="container-fluid timeline-row-container pdtb-default" id="timeline-row-container">
				<div class="container">
					<div class="row">
						<?php
							foreach($timelines as $timeline) {
							$icon = $timeline['timeline_icon'];?>
							<div class="col-md-4">
								<div class="text-center timeline-row-title mrtb30">
									<strong><?php echo $timeline['timeline_title'];?></strong>
								</div>
								<div class="col-md-12 timeline-row-text">
									<div class="timeline-row-icon">
										<?php echo get_icon($icon, true);?>
									</div> 									
									<?php echo $timeline['timeline_text'];?>
								</div>
							</div>
						<?php }?>
					</div>
				</div>
			</div>
<?php }