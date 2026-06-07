<?php

namespace SeopressComposer\Components;

use SeopressComposer\Services\IconService;

/**
 * SocialIcons Component
 * Renders social media and contact icon buttons
 */
class SocialIcons
{
  private IconService $iconService;

  public function __construct(IconService $iconService)
  {
    $this->iconService = $iconService;
  }

  /**
   * Render social/contact icons
   * 
   * @param array $options Configuration options
   * @return string
   */
  public function render(array $options = []): string
  {
    $defaults = [
      'phone' => get_field('telefonnummer', 'option') ?: '+43 676 681 20 90',
      'email' => get_field('email', 'option') ?: 'office@entruempelung-wien-noe.at',
      'whatsapp' => get_field('mobilnummer', 'option') ?: '+43 676 681 20 90',
      'container_class' => 'flex gap-3',
      'show_phone' => true,
      'show_email' => true,
      'show_whatsapp' => true
    ];

    $config = array_merge($defaults, $options);

    ob_start();
?>
    <div class="<?= esc_attr($config['container_class']) ?>">
      <?php if ($config['show_phone']): ?>
        <!-- Phone -->
        <a href="tel:<?= esc_attr($config['phone']) ?>" class="group" aria-label="Telefon">
          <div class="bg-white/10 p-2 rounded-full group-hover:bg-primary group-hover:text-white transition-all w-10 h-10 flex items-center justify-center text-white">
            <div class="w-5 h-5 flex items-center justify-center">
              <?= $this->iconService->getIcon('telefon', ['inner_class' => 'fill-current']) ?>
            </div>
          </div>
        </a>
      <?php endif; ?>

      <?php if ($config['show_email']): ?>
        <!-- Email -->
        <a href="mailto:<?= esc_attr($config['email']) ?>" class="group" aria-label="Email">
          <div class="bg-white/10 p-2 rounded-full group-hover:bg-primary group-hover:text-white transition-all w-10 h-10 flex items-center justify-center text-white">
            <div class="w-5 h-5 flex items-center justify-center">
              <?= $this->iconService->getIcon('email', ['inner_class' => 'fill-current']) ?>
            </div>
          </div>
        </a>
      <?php endif; ?>

      <?php if ($config['show_whatsapp']): ?>
        <!-- WhatsApp -->
        <a href="https://wa.me/<?= preg_replace('/[^0-9]/', '', $config['whatsapp']) ?>"
          target="_blank" rel="noopener noreferrer" class="group" aria-label="WhatsApp">
          <div class="bg-white/10 p-2 rounded-full group-hover:bg-green-500 group-hover:text-white transition-all w-10 h-10 flex items-center justify-center text-white">
            <div class="w-5 h-5 flex items-center justify-center">
              <?= $this->iconService->getIcon('whatsapp', ['inner_class' => 'fill-current']) ?>
            </div>
          </div>
        </a>
      <?php endif; ?>
    </div>
<?php
    return ob_get_clean();
  }
}
