<?php
function top_picture_clean(){	
   include(get_template_directory() .'/inc/core/components/top_picture/top_picture-model.php');
   //$url = wp_get_attachment_url(get_post_thumbnail_id(check_page_id()));?>
     <div class="container-fluid top-picture hidden-xs" style="background: url(<?php echo $top_picture['top_picture_image']; ?>);">
          <div class="page-image-title">
             <div class="page-image-title-overlay container">
               <h1><?php echo $top_picture['top_picture_title'] ?></h1>
               <h3><?php echo claim_clean(); ?></h3>
               <p class="claim-sub">in ganz <?php echo get_location();?></p>
               <a href="/kontakt" title="jetzt Kontakt aufnehmen und gratis Angebot oder Besichtigungstermin einholen">
                  <div class="cta-round hidden-xs primary-background-color"><span class="gratis">Jetzt</span> Angebot einholen</div>
               </a>
             </div>
          </div>
         </div>
<?php }

function top_picture_small(){	
   include(get_template_directory() .'/inc/core/components/top_picture/top_picture-model.php');
   //$url = wp_get_attachment_url(get_post_thumbnail_id(check_page_id()));?>
      <div class="container-fluid top-picture-small hidden-xs" style="background: url(<?php echo $top_picture['top_picture_image']; ?>);">
          <div class="page-image-title-small">
             <div class="page-image-title-overlay container">
               <h1><?php echo $top_picture['top_picture_title'] ?></h1>
             </div>
          </div>
      </div>
<?php }