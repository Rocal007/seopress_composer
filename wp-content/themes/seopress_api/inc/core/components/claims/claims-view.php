<?php function claim_clean() {
include(get_template_directory() .'/inc/core/components/claims/claims-model.php');
                return $claims[0]["claim_content"];
}