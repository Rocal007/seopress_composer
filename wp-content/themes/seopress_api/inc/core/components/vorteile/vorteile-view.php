<?php
function vorteile_clean_list($count){	
        include(get_template_directory() .'/inc/core/components/vorteile/vorteile-model.php');?>
        <div class="container-fluid vorteile pdtb-default" id="vorteile">
            <div class="container" id="vorteile-container">
                <h4 class="text-center vorteile-heading">Ihre Vorteile</h4>
                <?php for($i = 0; $i < $count; ++$i) {?>
           		
                    <div class="row accordation-row vorteile-accordation">
                        <div class="col-md-1 icon hidden-xs">
                            <?php echo get_icon($vorteile[$i]['vorteil_icon'])?>
                        </div>
                        <div class="col-md-11">
                            <div class="vorteil-heading" data-toggle="collapse" data-parent="#accordion" data-target="#vorteil-<?php echo $i; ?>">
                                    <?php echo $vorteile[$i]["vorteil_heading"]; ?>
                            </div>
                            <div id="vorteil-<?php echo $i; ?>" class="collapse vorteile">
                                        <?php echo $vorteile[$i]["vorteil_content"]; ?>
                            </div> 
                        </div>
                    </div>

            <?php };?>
    </div>
</div>
<?php };

function vorteile_clean_colum(){	
      	include(get_template_directory() .'/inc/core/components/vorteile/vorteile-model.php');?>
            <div class="container-fluid vorteile pdtb-default" id="vorteile-colum">
                <div class="container">
                    <h4 class="text-center vorteile-heading">Ihre Vorteile</h4>
                    <?php foreach($vorteile as $key => $vorteil) {?>
                                                           
                                <div class="col-md-6 vorteil-colum">
                                    <div class="vorteil-icon icon">
                                        <?php echo get_icon($vorteil['vorteil_icon']);?>
                                    </div>
                                    <div class="vorteil">
                                        <div class="vorteil-heading">
                                                <?php echo $vorteil["vorteil_heading"]; ?>
                                        </div>
                                        <div class="vorteil-content-colum">
                                                <?php echo $vorteil["vorteil_content"]; ?>
                                        </div> 
                                    </div>
                                </div>
                            
                        <?php };?>
                </div>
            </div>
<?php };