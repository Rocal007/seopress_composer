<?php
function images_clean($count){	
    include(get_template_directory() .'/inc/core/components/images/images-model.php');?>
                <div class="container-fluid images" id="images">
                    <div class="container images-container">
                        <h4 class="text-center">Bilder</h4>
                            <?php if (amp_is_request()){?>
                                <amp-accordion expand-single-section>
                                    <?php for($i = 0; $i < $count; ++$i) {?>
                                            <section>
                                                
                                            </section>
                                    <?php }?>
                                </amp-accordion>
                            <?php   
                            }
                            else {
                            for($i = 0; $i < $count; ++$i) {?>
                            <section>
                                    <div class="row">
                                    <div class="col-md-12">
                                            <div class="image">
                                                <?php echo $images[$i]["image_url"]; ?>
                                            </div>
                                            <div class="image-caption">
                                                <?php echo $images[$i]["image_caption"]; ?>
                                            </div> 
                                        </div>
                                    </div>
                                </section>            
                                <?php }
                            };?>
                    </div>
                </div>
<?php }?>