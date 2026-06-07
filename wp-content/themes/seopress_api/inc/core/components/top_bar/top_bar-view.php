<?php function topbar_clean() {
    include(get_template_directory() .'/inc/core/components/top_bar/top_bar-model.php');?> 
    <div class="container-fluid topbar secondary-background-color">
        <div class="container">
            <div class="row">
           
                <!--mobile -->
                            <?php if ( amp_is_request() ) {?>
                                    <div class="col-md-6 topbar-mobile-left">
                                        <a href="<?php echo esc_url( home_url( '/' ) );?>" rel="home" title=" <?php echo $site_title;?>">
                                            <h3 class="topbar-mobile-header">
                                                <?php echo $site_title;?>
                                            </h3>
                                        </a>
                                    </div>
                                    <div class="col-md-6 topbar-mobile-right">
                                                
                                        <amp-sidebar id="sidebar-left" class="sidebar-mobile" layout="nodisplay" side="left">
                                            <button on="tap:sidebar-left.close">X</button>
                                                <nav toolbar="(min-width: 784px)" toolbar-target="target-element-left">
                                                    <?php wp_nav_menu( array(
                                                        'container'       => 'div',
                                                        'container_class' => 'mobile-menue-container',
                                                        'menu' => 'Mobile Menu'
                                                    ) ); ?>
                                                </nav>
                                        </amp-sidebar>

                                        <div class="toggle-button" on="tap:sidebar-left.toggle">
                                            <svg viewBox="0 0 100 80" width="40" height="40">
                                                <rect class="fill-white" width="100" height="10"></rect>
                                                <rect class="fill-white" y="30" width="100" height="10"></rect>
                                                <rect class="fill-white" y="60" width="100" height="10"></rect>
                                            </svg>
                                        </div>
                                    
                                        <div id="target-element-left"></div>
                                                
                                    </div>
                            <?php }?>
                <!--mobile End-->



                <!-- desktop -->
                                        <div class="col-md-6 topbar-left text-left hidden-xs">
                                            <a title="Rufen sie uns an" href="tel:<?php echo get_options()['mobile_number']; ?>">
                                                <span class="fa fa-phone bgtoph-icon-clr"></span>
                                                <span class="topbar-text"><?php echo get_options()['mobile_view_number']; ?></span>
                                            </a>
                                            <a title="E-Mail" href="mailto:<?php echo get_options()['email']; ?>">
                                                <span class="fa fa-envelope bgtoph-icon-clr"></span>
                                                <span class="topbar-text"><?php echo get_options()['email']; ?></span>
                                            </a>
                                        </div>
                                        
                                        <div class="col-md-6 topbar-right text-right hidden-xs">
                                                    

                                        </div>
                <!-- desktop End-->                   
            </div>
        </div>
    </div>
<?php }