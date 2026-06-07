<?php
function faqs_clean($count){	
        include(get_template_directory() .'/assets/icons.php');
        include(get_template_directory() .'/inc/core/components/faq/faq-model.php');
?>
<div class="container-fluid pdtb-default" id="faq">
	<div class="container" id="accordion">
		<div class="h4 text-center">FAQ</div>
        <?php 
        if (amp_is_request()){?>
            <amp-accordion expand-single-section>
                <?php 
                for($i = 0; $i < $count; ++$i) {?>
                <section>
                    <h5><?php echo $faqs[$i]["faq_heading"]; ?></h5>
                    <p><?php echo $faqs[$i]["faq_content"]; ?></p>
                </section>
                <?php }?>
            </amp-accordion>
             <?php   
            }
            else {
            
            for($i = 0; $i < $count; ++$i) {?>
               
				<section>
                    <div class="row accordation-row faq-accordation">
                        
                        <div class="col-md-1 icon hidden-xs">
                            <?php echo $faq_icon;?>
                        </div>
                        <div class="col-md-11">
                            <div class="faq-heading" data-toggle="collapse" data-parent="#accordion" data-target="#faq-<?php echo $i; ?>">
                                    <?php echo $faqs[$i]["faq_heading"]; ?>
                                    <i class="indicator glyphicon glyphicon glyphicon-circle-arrow-down pull-right"></i>
                            </div>
                            <div id="faq-<?php echo $i; ?>" class="collapse faq">
                                        <?php echo $faqs[$i]["faq_content"]; ?>
                                <?php if ($faqs[$i]["faq_extension"]){?>
                                <div class="faq-extension-indicator" data-toggle="collapse" data-target="#faq-extension-<?php echo $i; ?>">
                                        <button class="btn btn-default ">
                                            mehr erfahren
                                        </button>
                                </div> 
                                <div id="faq-extension-<?php echo $i; ?>" class="collapse faq-extension-<?php echo $i; ?>">
                                    <?php echo $faqs[$i]["faq_extension"]; ?>
                                </div>
                                <?php }?>
                            </div>
                        </div>
                        
                    </div>
                </section>
            <?php }};?>
    </div>
</div>
<?php };