<?php
$components = seopress_components();
$models = seopress_models();
?>
<footer>


  <?php get_template_part('template-parts/footer-widgets'); ?>

  <?php
  echo $components->getFooterLinks()->render($models->getFooterLinksData());
  echo $components->getContactBar()->render($models->getContactData());
  ?>
  <?php //echo $components->getFooterWave()->render();
  ?>

  <!-- Floating Components -->

  <!-- Scroll to Top Button (CSS-only) -->
  <a href="#top" 
     class="fixed bottom-20 lg:bottom-8 right-4 z-40 btn btn-circle btn-primary shadow-lg hover:shadow-xl opacity-70 hover:opacity-100 transition-all"
     aria-label="Nach oben scrollen"
     title="Nach oben">
    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 15l7-7 7 7"/>
    </svg>
  </a>

  <?php wp_footer(); ?>
</footer>
</body>

</html>