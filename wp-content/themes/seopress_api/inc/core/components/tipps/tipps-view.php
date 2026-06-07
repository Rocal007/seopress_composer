<?php
function tips_clean($count){	
        include(get_template_directory() .'/assets/icons.php');
        include(get_template_directory() .'/inc/core/components/tipps/tipps-model.php');
?>
<div class="container-fluid pdtb-default" id="tipps">
	<div class="container accordation-container" id="accordion">
		<div class="h4 text-center">TIPPS</div>
            <?php if (amp_is_request()){?>
                <amp-accordion expand-single-section>
                    <?php for($i = 0; $i < $count; ++$i) {?>
                            <section>
                                <h5><?php echo $tips[$i]["tip_heading"]; ?></h5>
                                <p><?php echo $tips[$i]["tip_content"]; ?></p>
                            </section>
                    <?php }?>
                </amp-accordion>
             <?php   
            }
            else {
            for($i = 0; $i < $count; ++$i) {?>
               <section>
                    <div class="row">
                        <div class="col-md-1 icon hidden-xs">
                            <?php echo $faq_icon;?>
                        </div>
                        <div class="col-md-11">
                            <div class="tip-heading" data-toggle="collapse" data-parent="#accordion" data-target="#tip-<?php echo $i; ?>">
                                    <?php echo $tips[$i]["tip_heading"]; ?>
                                    <i class="indicator glyphicon glyphicon-chevron-down  pull-right"></i>
                            </div>
                            <div id="tip-<?php echo $i; ?>" class="collapse tip">
                                        <?php echo $tips[$i]["tip_content"]; ?>
                                <?php if ($tips[$i]["tip_extension"]){?>
                                <div class="btn-default tip-extension-indicator" data-toggle="collapse" data-target="#tip-extension-<?php echo $i; ?>">
                                        
											 mehr erfahren
                                </div> 
                                <div id="tip-extension-<?php echo $i; ?>" class="collapse tip-extension-<?php echo $i; ?>">
                                    <?php echo $tips[$i]["tip_extension"]; ?>
                                </div>
                                <?php }?>
                            </div>
                        </div>
                    </div>
                </section>            
                <?php }
            };?>
    </div>
</div>
<?php }?>