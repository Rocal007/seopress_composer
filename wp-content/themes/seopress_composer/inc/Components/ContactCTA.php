<?php

namespace SeopressComposer\Components;

use SeopressComposer\Services\IconService;
use SeopressComposer\Helpers\PageHelper;

class ContactCTA
{
  private IconService $iconService;
  private PageHelper $pageHelper;

  public function __construct(IconService $iconService, PageHelper $pageHelper)
  {
    $this->iconService = $iconService;
    $this->pageHelper = $pageHelper;
  }

  /**
   * Render the Contact CTA component
   *
   * @param string|null $serviceName Optional service name to display in the interest heading
   * @param string|null $customHeading Optional custom heading (overrides default)
   * @param string|null $customDescription Optional custom description (overrides default)
   * @return string HTML output
   */
  public function render(
    ?string $serviceName = null,
    ?string $customHeading = null,
    ?string $customDescription = null
  ): string {
    // Get Phone Number
    $business = $this->pageHelper->get_options_business();
    $phone = $business['telefonnummer'] ?? '+43 676 681 20 90';

    // Prepare heading
    if ($customHeading) {
      $heading = $customHeading;
    } else {
      $heading = 'Möchten Sie ';
      if ($serviceName) {
        $heading .= '' . esc_html($serviceName) . ' verkaufen';
      }
      $heading .= '?';
    }

    // Prepare description
    $description = $customDescription ?? 'Kontaktieren Sie uns jetzt für eine kostenlose Beratung und ein unverbindliches Angebot.';

    // Get Icons - Use default 'illu' class and 'formular' icon
    $phoneIcon = $this->iconService->getIcon('telefon', ['size' => 'md']);
    $mailIcon = $this->iconService->getIcon('formular', ['size' => 'md']);

    ob_start();
?>
    <!-- Contact CTA -->
    <div class="max-w-3xl mx-auto text-center">
      <h2 class="text-3xl font-bold mb-6 text-base-content">
        <?= $heading ?>
      </h2>
      <p class="text-lg text-base-content/70 mb-8">
        <?= esc_html($description) ?>
      </p>

      <div class="flex flex-wrap justify-center gap-4">
        <a href="tel:<?= esc_attr($phone) ?>" class="btn btn-primary btn-lg gap-2 min-w-[220px] shadow-lg hover:shadow-xl transition-all">
          <?= $phoneIcon ?>
          <span>
            <span class="block font-bold">Jetzt kostenlos anrufen</span>
            <span class="block text-xs opacity-80 font-normal"><?= esc_html($phone) ?></span>
          </span>
        </a>
        <a href="<?= esc_url(home_url('/kontakt')) ?>" class="btn btn-outline btn-primary btn-lg gap-2 min-w-[220px] hover:shadow-lg transition-all">
          <?= $mailIcon ?>
          Schreiben Sie uns
        </a>
      </div>
      <p class="text-sm text-base-content/50 mt-4">✓ Kostenlose Beratung · ✓ Unverbindliches Angebot · ✓ Schnelle Rückmeldung</p>
    </div>
<?php
    return ob_get_clean();
  }
}
