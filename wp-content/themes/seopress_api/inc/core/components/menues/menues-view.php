<?php
function menu_clean(){
    include(get_template_directory() .'/inc/core/components/menues/menues-model.php');?>
    <div class="hidden-xs">
        <div class="footer-heading">Leistungen</div>
            <ul>
                <?php foreach ($main_menu as $menu_item) {?>

                <li>
                    <a title="<?php echo $menu_item['menu_linked_title']?>" href="<?php echo $menu_item['menu_link']?>">
                        <?php echo $menu_item['menu_title']?>
                    </a>
                </li>

                <?php } ?>
            </ul>
       </div>
<?php }

/*Startseite*/
function home_row_menue(){
    include(get_template_directory() .'/inc/core/components/menues/menues-model.php');?>
    <div class="container-fluid home_row_menue-container pdtb-default" id="home_row_menue">
        <div class="container">
            <div class="col-md-12 row_menu">
                <?php foreach ($main_menu as $main_menu_item) {?>
                                        
                    <div class="col-md-2 col-xs-6 row-menue-item">
                        <a href="<?php echo $main_menu_item['menu_link']?>" title="<?php echo $main_menu_item['menu_linked_title']; ?>">
                            <div class="row_menu-image icon">
                                <?php echo get_icon($main_menu_item['menu_icon'], true); ?>
                            </div>
                            <p class="row_menu-title text-center">
                                <?php echo $main_menu_item['menu_title']?>
                                <span style="display:none"><?php echo $main_menu_item['menu_location']?></span>
                            </p>
                        </a>
                    </div>
    
                <?php }; ?>
            </div>   
        </div>
    </div>
<?php }

function home_image_menue(){
    include(get_template_directory() .'/inc/core/components/menues/menues-model.php');?>
        <div class="container-fluid home_row_menue-container pdtb-default" id="home_image_menue">
            <div class="container">

                <?php foreach ($main_menu as $menu_item) {?>

                    <div class="row">
                        <div class="col-md-4 image-menue-img">
                            <a href="<?php echo $menu_item['menu_link']?>" title="<?php echo $menu_item['menu_linked_title']; ?>">
                                <figure>
                                        <img src="<?php echo $menu_item['menu_image_url']?>" alt="<? echo $menu_item['menu_linked_title']?>">
                                        <!--<figcaption><? echo $menu_item['menu_linked_title']?></figcaption>-->
                                </figure> 
                            </a>
                        </div>   
                        <div class="col-md-8 col-xs-6 image-menue-text">
                            <a href="<?php echo $menu_item['menu_link'] ?>" title="<?php echo $menu_item['menu_linked_title'] ?>">
							
                                <div class="image-menue-icon icon pull-left">
                                    <?php echo get_icon($menu_item['menu_icon'], true) ?>
                                </div>
                                <div class="h5 image-menue-title">
                                    <?php echo $menu_item['menu_title'] ?>
                                    <sup><?php echo $menu_item['menu_location'] ?></sup>
                                </div>
                            </a>
                                <?php echo get_overview_text($menu_item['remote_url']); ?>
                            
                        </div> 
                    </div> 

                <?php } ?>
            </div>   
        </div>
<?php }

/*Kathegorien*/

function cat_row_menue(){
    include(get_template_directory() .'/inc/core/components/menues/menues-model.php');?>
        <div class="container-fluid cat_row_menue-container pdtb-default" id="cat_row_menue">
            <div class="container">
                <div class="col-md-12 row_menu">

                    <?php foreach ($cat_menu as $cat_menu_item) {?>
                                            
                        <div class="col-md-2 col-xs-6 row-menue-item">
                            <a href="<?php echo $cat_menu_item['cat_menu_link']?>" title="<?php echo $cat_menu_item['cat_menu_linked_title']; ?>">
                                <div class="row_menu-image icon">
                                    <?php echo get_icon($cat_menu_item['cat_menu_icon'], true); ?>
                                </div>
                                <p class="row_menue-title text-center">
                                    <?php echo $cat_menu_item['cat_menu_title']?>
                                    <span style="display:none"><?php echo $cat_menu_item['menu_location']?></span>
                                </p>
                            </a>
                        </div>
        
                    <?php }; ?>
                </div>   
            </div>
        </div>
<?php }

function cat_image_menue(){
    include(get_template_directory() .'/inc/core/components/menues/menues-model.php');?>
        <div class="container-fluid cat_row_menue-container pdtb-default" id="cat_image_menue">
            <div class="container">

                <?php foreach ($cat_menu as $cat_menu_item) {?>

                    <div class="row mrb20">
                        <a class="hidden-lg hidden-md" href="<?php echo $cat_menu_item['cat_menu_link']?>" title="<?php echo $cat_menu_item['cat_menu_linked_title']; ?>">
                                    <div class="image-menue-icon icon pull-left">
                                        <?php echo get_icon($cat_menu_item['cat_menu_icon'], true); ?>
                                    </div>
                                    <div class="h5 image-menue-title">
                                        <?php echo $cat_menu_item['cat_menu_title']?>
                                        <sup><?php echo $cat_menu_item['cat_menu_location']?></sup>
                                    </div>
                        </a>
                        <div class="col-md-4 col-xs-12 image-menue-img">
                            <a href="<?php echo $cat_menu_item['cat_menu_link']?>" title="<?php echo $cat_menu_item['cat_menu_linked_title']; ?>">
                                <figure>
                                        <img src="<?php echo $cat_menu_item['cat_menu_image_url']?>" alt="<? echo $cat_menu_item['cat_menu_linked_title']?>">
                                        <!--<figcaption><? echo $cat_menu_item['cat_menu_linked_title']?></figcaption>-->
                                </figure> 
                            </a>
                        </div>   
                        <div class="col-md-8 col-xs-12 image-menue-text">
                            <a class="hidden-xs" href="<?php echo $cat_menu_item['cat_menu_link']?>" title="<?php echo $cat_menu_item['cat_menu_linked_title']; ?>">
                                <div class="image-menue-icon icon pull-left">
                                    <?php echo get_icon($cat_menu_item['cat_menu_icon'], true); ?>
                                </div>
                                <div class="h5 image-menue-title">
                                    <?php echo $cat_menu_item['cat_menu_title']?>
                                    <sup><?php echo $cat_menu_item['cat_menu_location']?></sup>
                                </div>
							</a>
                                    <?php echo get_overview_text($cat_menu_item['remote_url']); ?> 
                        </div> 
                    </div>    

                <?php } ?>
            </div>   
        </div>
<?php }