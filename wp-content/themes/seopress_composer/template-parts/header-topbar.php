<?php

/**
 * Template part for displaying the topbar
 */

// Get Services
$components = \seopress_components();
$models = \seopress_models();

// Get Data
$topbarData = $models->getTopbarData();

// Render
echo $components->getTopbar()->render($topbarData);
