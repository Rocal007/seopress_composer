<?php

namespace SeopressComposer\Components;

use SeopressComposer\Services\IconService;
use SeopressComposer\Components\SocialIcons;

class ContactFooter
{
  private IconService $iconService;
  private SocialIcons $socialIcons;

  public function __construct(IconService $iconService, SocialIcons $socialIcons)
  {
    $this->iconService = $iconService;
    $this->socialIcons = $socialIcons;
  }

  /**
   * Render the Contact Footer component
   *
   * @param array $data Data from Contact_Data model
   * @return string HTML output
   */
  public function render(array $data): string
  {
    $site_name = $data['firmenname'] ?: ($data['site_name'] ?? get_bloginfo());
    $phone = $data['phone_number'] ?? '';
    // $phone_view = $data['phone_number_view'] ?? $phone; // Unused with SocialIcons defaults usually
    $email = $data['email'] ?? '';
    $uid = $data['uid'] ?? '';
    $address = $data['address'] ?? [];
    $firmenbuchnummer = $data['firmenbuchnummer'] ?? '';
    $gerichtsstand = $data['gerichtsstand'] ?? '';
    $inhaber = $data['inhaber'] ?? '';
    $gln = $data['gln'] ?? '';

    // Prepare options for SocialIcons
    $socialOptions = [
      'container_class' => 'flex gap-3 mb-4',
      'phone' => $phone,
      'email' => $email,
      // We can pass other fields if SocialIcons supports them or relies on strict defaults
    ];

    ob_start();
?>
    <div class="footer-contact">
      <!-- Logo/Title -->
      <h3 class="text-xl font-bold mb-2 text-white">
        <?= esc_html($site_name) ?>
      </h3>
      <?php if ($desc = get_bloginfo('description')): ?>
        <p class="text-sm opacity-80 mb-6 max-w-xs leading-relaxed">
          <?= esc_html($desc) ?>
        </p>
      <?php else: ?>
        <div class="mb-4"></div>
      <?php endif; ?>

      <div class="flex flex-col gap-4 text-white/90">
        <!-- Social Icons (Phone, Email, WA) -->
        <?= $this->socialIcons->render($socialOptions) ?>




        <!-- Legal Menu (Moved from Col 3) -->
        <?php if (!empty($data['legal_menu_html'])) : ?>
          <div class="mt-6">
            <?= $data['legal_menu_html'] ?>
          </div>
        <?php endif; ?>
      </div>
    </div>
<?php
    return ob_get_clean();
  }
}
