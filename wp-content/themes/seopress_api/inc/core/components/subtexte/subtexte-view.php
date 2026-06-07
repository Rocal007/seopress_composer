<?php 
function subtext_clean($index) {
    include(get_template_directory() .'/inc/core/components/subtexte/subtexte-model.php');?>

    <div class="container-fluid subtexte">
        <div class="container inner-container">
            <div class="subtext row">
                <div class="subtext-heading">
                    <?php echo $subtexte[$index]["subtext_heading"]; ?>
                </div>
                <div class="subtext-content">
                    <?php echo $subtexte[$index]["subtext_content"]; ?>
                    <?php //echo $subtexte_hs[$index]["subtext_extension"]; ?>
                </div>
            </div>
        </div>
	</div>
<?php }; 

function subtext_hs_clean($index_hs) {
    include(get_template_directory() .'/inc/core/components/subtexte/subtexte-model.php');?>

    <div class="container-fluid subtexte">
        <div class="container inner-container">
            <div class="subtext row">
                <div class="subtext-heading">
                    <?php echo $subtexte_hs[$index_hs]["subtext_heading"]; ?>
                </div>
                <div class="subtext-content">
                    <?php echo $subtexte_hs[$index_hs]["subtext_content"]; ?>
                    <?php //echo $subtexte_hs[$index]["subtext_extension"]; ?>
                </div>
            </div>
        </div>
	</div>
<?php }; ?>