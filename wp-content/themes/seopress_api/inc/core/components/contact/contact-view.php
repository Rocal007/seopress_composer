<?php
function contact_menu(){	
wp_reset_query();
        $contact_menue = 
        '<div class="footer-contact">
            <div class="footer-contact-heading">' . get_bloginfo('name') . '</div>
            <div class="footer-contact-claim">schnell | fachgerecht | g&uuml;nstig</div>    
            <div class="footer-claim">Einsatzgebiete in ' . get_options()['bundesland'] . '<br> und ganz Österreich</div> 
                <div class="footer-contact-adress"> 
                    <!--<div class="street">'. get_options()['street'] .' | '. get_options()['zip'] .'</div>-->
					<div class="uid">UID: '. get_options()['uid'] . '</div>
                </div>
		</div>';
	return $contact_menue;
}

function contactMenu_icons() {    
	wp_reset_query();
    return '<div id="footer-colum footer-contact">
            <div class="footer-heading">Kontakt</div>
                 <div class="row"><div class="col-xs-3 footer-icon">'.get_icon('Telefon', true).'</div><div class="col-xs-9 footer-contact-text"><a title="Jezt anrufen und Termin mit '. get_the_title(get_the_id()) .' vereinbaren" href="tel:' . get_options()['mobile_number'] . '">'. get_options()['mobile_view_number'] .'</a></div></div>
                 <div class="row"><div class="col-xs-3 footer-icon">'.get_icon('Formular', true).'</div><div class="col-xs-9 footer-contact-text"><a href="/kontakt">Kontaktformular</a></div></div>
                 <div class="row"><div class="col-xs-3 footer-icon">'.get_icon('E-Mail', true).'</div><div class="col-xs-9 footer-contact-text"><a href="mailto:' . get_options()['email'] . '">E-Mail</a></div></div>
                 <div class="row"><div class="col-xs-3 footer-icon">'.get_icon('Whatsapp', true).'</div><div class="col-xs-9 footer-contact-text"><a href="https://api.whatsapp.com/send?phone=' . get_options()['mobile_number'] . '&text='. get_bloginfo('name') .'">Whats App</a></div></div>
            </div>';
 }

function contact_oeffnugszeiten() {	
wp_reset_query();
    return '<div id="footer-colum footer-contact footer-times">
            <div class="footer-heading">Öffnungszeiten</div>
                
            </div>';
}

function contact_line() {
wp_reset_query();
?>
<div class="container-fluid text-center hidden-xs secondary-background-color">
	<div class="container contact-line-container">
		<div class="row">
            <div class="col-md-4">
                <a title="Jezt anrufen und Termin mit <?php echo get_the_title(get_the_id()); ?> vereinbaren" href="tel:<?php echo get_options()['mobile_number'];?>">
                    <div class="text-center btn btn-block btn-default invert-text-color primary-background-color">
                        <div class="h5"><?php echo get_options()['mobile_view_number'];?></div>
                    </div>
                </a>
            </div>
            <div class="col-md-4">
                <a title="Schreiben Sie, schicken Sie uns Fotos oder vereinbaren Sie einen Termin mit <?php echo get_the_title(get_the_id()); ?>" href="/kontakt">
                    <div class="text-center btn btn-block btn-default invert-text-color primary-background-color">
						<div class="h5">Kontakt</div>
                    </div>
                </a>
            </div>
            <div class="col-md-4">
                <a title="Schreiben Sie, schicken Sie uns Fotos oder vereinbaren Sie einen Termin mit <?php echo get_the_title(get_the_id()); ?>" href="mailto:<?php echo get_options()['email'];?>">
                    <div class="text-center btn btn-block btn-default invert-text-color primary-background-color">
                        <div class="h5">E-Mail</div>
                    </div>
                </a>
            </div>
		</div>
	</div>
</div>
<?php }

function contactMenu_header() {
wp_reset_query();?>

	<div class="header-phone pull-left">
	    <a title="Jezt anrufen und Termin mit <?php the_title(); ?> vereinbaren" href="tel:<?php echo get_options()["phone_number"];?>">
            <span><?php echo get_options()["phone_number_view"];?></span>
        </a>	
	</div>
    <div class="contactbox pull-right">
        <a title="Jezt anrufen und Termin mit <?php the_title(); ?> vereinbaren" href="tel:<?php echo get_options()["mobile_number"];?>">
            <div class="contact-icon">
                <?php echo get_icon('Telefon', true);?>
            </div>
        </a>
        <a title="Schreiben Sie uns eine E-mail und vereinbaren Sie einen unverbindlichen Besichtigungstermin mit <?php the_title(); ?>" href="mailto:<?php echo get_options()['email'];?>">
            <div class="contact-icon">
                <?php echo get_icon('E-Mail', true);?>
            </div>
        </a>
        <a title="Verwenden Sie unser Kontaktformular, schicken Sie uns Fotos, vereinbaren Sie einen Rückruf,... mit <?php the_title(); ?>" href="/kontakt">
            <div class="contact-icon">
                <?php echo get_icon('Formular', true);?>
            </div>
        </a>
        <a title="Kontaktieren Sie <?php the_title(); ?> auch per WhatsApp, schreiben Sie uns und schicken Sie Fotos" href="https://api.whatsapp.com/send?phone=<?php echo get_options()['mobile_number'];?>&amp;text=Anfrage <?php the_title(); ?>">
            <div class="contact-icon">
                <?php echo get_icon('Whatsapp', true);?>
            </div>
        </a>
    </div>
<?php }

function contactMenu_header_new() {
    wp_reset_query();?>

    <div class="col col-lg-6 hidden-md hidden-sm hidden-xs">
        <a id="contact-call-link" rel=”nofollow” title="Jetzt anrufen und Termin mit <?php the_title(); ?> vereinbaren" href="tel:<?php echo get_options()['phone_number'];?>">
            <div class="col-lg-8">
                <div class="contact-button primary-background-color">
                    <div class="contact-icon col-lg-4">
                        <?php echo get_icon('Telefon',false, true, true);?>
                    </div>
                    <div class="col-lg-8 invert-text-color">
                        <h4>ANRUFEN</h4>
                    </div>
                </div>
            </div>
        </a>
        <div class="col-lg-3">
            <div class="contact-dropdown">
                <div class="contact-button burgerdiv dropdown-toggle" data-toggle="dropdown">
                    <svg viewBox="0 0 100 80" width="40" height="40">
                        <rect class="primary-background bar1" width="100" height="10"></rect>
                        <rect class="primary-background bar2" y="30" width="100" height="10"></rect>
                        <rect class="primary-background bar3" y="60" width="100" height="10"></rect>
                    </svg>
                </div>
                <div id="contact-dropdown-content" class="contact-dropdown-content dropdown-menu dropdown-menu-right">
                    <a class="" href="https://api.whatsapp.com/send?phone=<?php echo get_options()['mobile_number'];?>&amp;text=Anfrage <?php the_title(); ?>">
                        <div class="contact-dropdown-item">
                            <div class="contact-icon col-lg-4">
                                <?php echo get_icon('Whatsapp');?>
                            </div>
                            <div class="col-lg-8">WhatsApp</div>
                        </div>
                    </a>
                    <a class="" href="/kontakt">
                        <div class="contact-dropdown-item">
                            <div class="contact-icon col-lg-4">
                                <?php echo get_icon('Formular');?>
                            </div>
                            <div class="col-lg-8">Kontakt</div>
                        </div>
                    </a>
                    <a class="" href="mailto:<?php echo get_options()['email'];?>">
                        <div class="contact-dropdown-item">
                            <div class="contact-icon col-lg-4">
                                <?php echo get_icon('E-Mail');?>
                            </div>
                            <div class="col-lg-8">Mail</div>
                        </div>
                    </a>
                </div>
            </div>
        </div>
        <div class="col-lg-1"></div>
    </div>
 <?php }

function contactMenu_header_new_row() {
    wp_reset_query();?>

    <div id="contact-dropdown-content-row" class="col col-xs-12 hidden-xs">
        <div class="col-lg-3"></div>
        <a id="contact-call-link" rel=”nofollow” title="Jetzt anrufen und Termin mit <?php the_title(); ?> vereinbaren" href="tel:<?php echo get_options()['phone_number'];?>">
            <div class="col-lg-3">
                <div class="contact-button primary-background-color">
                    <div class="contact-icon col-lg-4">
                        <?php echo get_icon('Telefon',false, true, true);?>
                    </div>
                    <div class="col-lg-8 invert-text-color">
                        <h4>ANRUFEN</h4>
                    </div>
                </div>
            </div>
        </a>
        <div class="col-lg-2"></div>
        <div class="col-lg-1">
            <div class="contact-dropdown">
                <div class="contact-button burgerdiv dropdown-toggle" data-toggle="dropdown">
                    <svg viewBox="0 0 100 80" width="40" height="40">
                        <rect class="primary-background bar1" width="100" height="10"></rect>
                        <rect class="primary-background bar2" y="30" width="100" height="10"></rect>
                        <rect class="primary-background bar3" y="60" width="100" height="10"></rect>
                    </svg>
                </div>
                <div id="contact-dropdown-content" class="contact-dropdown-content dropdown-menu dropdown-menu-right">
                    <a class="" href="https://api.whatsapp.com/send?phone=<?php echo get_options()['mobile_number'];?>&amp;text=Anfrage <?php the_title(); ?>">
                        <div class="contact-dropdown-item">
                            <div class="contact-icon col-lg-4">
                                <?php echo get_icon('Whatsapp');?>
                            </div>
                            <div class="col-lg-8">WhatsApp</div>
                        </div>
                    </a>
                    <a class="" href="/kontakt">
                        <div class="contact-dropdown-item">
                            <div class="contact-icon col-lg-4">
                                <?php echo get_icon('Formular');?>
                            </div>
                            <div class="col-lg-8">Kontakt</div>
                        </div>
                    </a>
                    <a class="" href="mailto:<?php echo get_options()['email'];?>">
                        <div class="contact-dropdown-item">
                            <div class="contact-icon col-lg-4">
                                <?php echo get_icon('E-Mail');?>
                            </div>
                            <div class="col-lg-8">Mail</div>
                        </div>
                    </a>
                </div>
            </div>
        </div>
        <div class="col-lg-3"></div>
    </div>
<?php }