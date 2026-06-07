<?php
function ablauf_clean(){
    include(get_template_directory() .'/inc/core/components/cta_boxes/cta_boxes-model.php');?>
<div class="container-fluid ablauf-container pdtb-default" id="ablauf">
    <div class="container">
      
        <div class="text-center">
			<h3>Ablauf - <?php echo $ablauf_boxes_title;?></h3>
        </div>
      
        <div class="row">

        <?php foreach($ablauf_boxes as $ablauf_box){?>

            <div class="col-sm-6 col-md-4">
                <div class="ablauf-box primary-border-color">
                    <div class="ablauf-icon">
                        <?php echo get_icon($ablauf_box["ablauf_icon"]);?>
                    </div>
                    <div class="ablauf-content">
                        
                        <div class="ablauf-heading text-center"><?php echo $ablauf_box["ablauf_heading"];?></div>
                        <div class="ablauf-text"><?php echo $ablauf_box["ablauf_content"];?></div>
                        <a title="<?php echo $ablauf_box["ablauf_button_link_text"];?>" href="<?php echo $ablauf_box["ablauf_link"];?>">
                            <div class="ablauf-button">
                               
                                    <button class="btn primary-background-color invert-text-color pull-right"><?php echo $ablauf_box["ablauf_button_text"];?></button>
                              
                            </div>
                        </a>
                    </div>
                </div>
                <div class="hidden-md">
                            <span class="badge invert-text-color primary-background-color"><?php echo $ablauf_box["index"];?></span>
                </div>
            </div>
            <?php } ?>
        <!--
            <div class="col-sm-6 col-md-4">
                <div class="ablauf-box primary-border-color">
                    <div class="ablauf-icon">
                        <?php echo get_icon("Festpreisangebot (Variante 1)");?>
                    </div>
                    <div class="ablauf-content">
                        <div class="ablauf-heading text-center">Angebot</div>
                        <div class="ablauf-text">Sie erhalten von uns ein Festpreis-Angebot mit Wertausgleich für alle vereinbarten Kosten ihrer Entrümpelung, Abbau & Transport. Es entstehen für Sie keine versteckten Kosten.</div>
                        <a title="Schicken Sie uns die wichtigsten Fakten und wir schreiben Ihnen gerne ein Angebot" href="/kontakt">
                            <div class="ablauf-button">
                            
                                <button class="btn primary-background-color invert-text-color pull-right">Angebot anfordern</button>
                            
                            </div>
                        </a>
                    </div>
                 </div>
                 <div class="hidden-md">
                    <span class="badge invert-text-color primary-background-color">2</span>
                </div>
            </div>
            <div class="col-sm-6 col-md-4">
                <div class="ablauf-box primary-border-color">
                    <div class="ablauf-icon">
                        <?php echo get_icon("Transport");?>
                    </div>
                    <div class="ablauf-content">
                        
                        <div class="ablauf-heading text-center">Durchführung</div>
                        <div class="ablauf-text">Ihre Entrümpelung pünktlich und mit allen vereinbarten Leistungen aus unserem Angebot. Nach Abschluss unserer Arbeit erhalten Sie ein besenreines Objekt.</div>
                        <a title="Sehen Sie sich gerne eine unserer Entrümpelungen an!" href="/entruempelungen-kaernten#video">
                            <div class="ablauf-button col-md-8">
                                <button class="btn primary-background-color invert-text-color pull-right">Beispiel ansehen</button>
                            </div>
                        </a>
                    </div>
                    
                </div>
                <div class="hidden-md">
                    <span class="badge invert-text-color primary-background-color">3</span>
                </div>
            </div>-->
               
            </div>
        </div>
    </div>
<?php }
