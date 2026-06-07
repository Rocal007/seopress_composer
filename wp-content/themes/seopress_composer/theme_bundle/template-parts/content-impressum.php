<?php
$components = seopress_components();
$data = seopress_models()->getImpressumData();
?>
<section class="py-24 lg:py-32 bg-base-100">
  <div class="container mx-auto px-4">
    <h1 class="text-4xl font-bold mb-8 text-secondary font-headline"><?= get_the_title() ?></h1>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
      <div class="prose max-w-none prose-lg">
        <?php if (!empty($data['company_name'])): ?>
          <h3 class="text-xl font-bold mb-2"><?= esc_html($data['company_name']) ?></h3>
        <?php endif; ?>

        <div class="mb-6">
          <?php if (!empty($data['street'])): ?>
            <p class="m-0"><?= esc_html($data['street']) ?></p>
          <?php endif; ?>
          <?php if (!empty($data['zip']) || !empty($data['city'])): ?>
            <p class="m-0"><?= esc_html($data['zip'] . ' ' . $data['city']) ?></p>
          <?php endif; ?>
          <?php if (!empty($data['country'])): ?>
            <p class="m-0"><?= esc_html($data['country']) ?></p>
          <?php endif; ?>
        </div>

        <div class="mb-6">
          <?php if (!empty($data['email'])): ?>
            <p class="m-0">
              <strong>E-Mail:</strong>
              <a href="mailto:<?= esc_attr($data['email']) ?>" class="text-primary hover:underline">
                <?= esc_html($data['email']) ?>
              </a>
            </p>
          <?php endif; ?>

          <?php if (!empty($data['phone'])): ?>
            <p class="m-0">
              <strong>Tel:</strong>
              <a href="tel:<?= esc_attr(str_replace(' ', '', $data['phone'])) ?>" class="text-primary hover:underline">
                <?= esc_html($data['phone']) ?>
              </a>
            </p>
          <?php endif; ?>
        </div>

        <div class="mb-6">
          <?php if (!empty($data['uid'])): ?>
            <p class="m-0"><strong>UID-Nr:</strong> <?= esc_html($data['uid']) ?></p>
          <?php endif; ?>
          <?php if (!empty($data['gln'])): ?>
            <p class="m-0"><strong>GLN:</strong> <?= esc_html($data['gln']) ?></p>
          <?php endif; ?>
          <?php if (!empty($data['industry'])): ?>
            <p class="m-0"><strong>Kammerzugehörigkeit:</strong> <?= esc_html($data['industry']) ?></p>
          <?php endif; ?>
        </div>

        <?php if (!empty($data['copyright'])): ?>
          <div class="mt-8 pt-6 border-t border-base-200">
            <p class="text-sm text-base-content/70"><?= nl2br(esc_html($data['copyright'])) ?></p>
          </div>
        <?php endif; ?>
      </div>

      <!-- Optional: Additional Content from Editor -->
      <div class="prose max-w-none prose-lg">
        <?php the_content(); ?>
      </div>
    </div>
  </div>
</section>