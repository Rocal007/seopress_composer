<?php

namespace SeopressComposer\Components;

/**
 * PricingCard Component (Pricing Table)
 * Enhanced with DaisyUI Table component and smart heading truncation
 */
class PricingCard
{
  /**
   * Format a price string to include thousand separators and a € sign.
   */
  private function formatPrice(string $price): string
  {
    $price = trim($price);
    if (empty($price) || $price === '-') {
      return $price;
    }

    // Format numbers >= 1000 with a dot
    $price = preg_replace_callback('/\b(\d{4,})\b/', function ($matches) {
      return number_format((float)$matches[1], 0, ',', '.');
    }, $price);

    // Add euro sign if missing and it contains digits
    if (preg_match('/\d/', $price) && strpos($price, '€') === false && stripos($price, 'euro') === false) {
      $price .= ' €';
    }

    return $price;
  }

  /**
   * Truncate heading if too long and show full text in tooltip
   * 
   * @param string $heading Full heading text
   * @param int $maxLength Maximum length before truncation
   * @return string HTML with heading and optional tooltip
   */
  private function renderHeading(string $heading, int $maxLength = 50): string
  {
    if (empty($heading)) {
      return '';
    }

    // Strip HTML tags for length calculation
    $plainText = strip_tags($heading);
    $needsTruncation = mb_strlen($plainText) > $maxLength;

    if (!$needsTruncation) {
      // Short enough, render as-is
      return wp_kses_post($heading);
    }

    // Truncate to maxLength characters
    $truncated = mb_substr($plainText, 0, $maxLength);

    // Try to break at last space to avoid cutting words
    $lastSpace = mb_strrpos($truncated, ' ');
    if ($lastSpace !== false && $lastSpace > $maxLength * 0.8) {
      $truncated = mb_substr($truncated, 0, $lastSpace);
    }

    $truncated = trim($truncated) . '...';

    // Return with DaisyUI tooltip
    ob_start();
?>
    <span class="tooltip tooltip-bottom cursor-help" data-tip="<?= esc_attr($plainText) ?>">
      <?= esc_html($truncated) ?>
      <svg class="inline-block w-5 h-5 ml-2 text-primary-focus" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
          d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
      </svg>
    </span>
  <?php
    return ob_get_clean();
  }

  use \SeopressComposer\Components\Traits\ModelRenderable;

  protected function getModelIndices(array $data): array
  {
    return ['wenig', 'normal', 'viel', 'messie'];
  }

  /**
   * Render multiple items from a data model by indices (columns).
   */
  public function renderManyFromModel(array $data, ?array $indices = null, ...$args): array
  {
    if ($indices === null) {
      $indices = $this->getModelIndices($data);
    }

    $results = [];
    foreach ($indices as $key) {
      $results[] = $this->renderOne($data, $key);
    }
    return $results;
  }

  /**
   * Render a single Pricing Card for a Category (Column)
   * $key = 'wenig' | 'normal' | 'viel' | 'messie'
   * Note: The $data passed here is the FULL array of rows.
   */
  public function renderOne(mixed $rows, string $key = 'wenig', ...$args): string
  {
    if (!is_array($rows))
      return '';

    // Define metadata for each column
    $meta = [
      'wenig' => ['title' => 'Wenig Hausrat', 'class' => 'border-primary', 'badge' => 'Wenig Hausrat', 'badge_class' => 'badge-primary'],
      'normal' => ['title' => 'Normaler Hausrat', 'class' => 'border-secondary', 'badge' => 'Normaler Hausrat', 'badge_class' => 'badge-secondary'],
      'viel' => ['title' => 'Viel Hausrat', 'class' => 'border-accent', 'badge' => 'Viel Hausrat', 'badge_class' => 'badge-accent'],
      'messie' => ['title' => 'Messie / Extrem', 'class' => 'border-error', 'badge' => 'Messie / Extrem', 'badge_class' => 'badge-error'],
    ];

    $info = $meta[$key] ?? $meta['wenig'];
    $title = $info['title'];
    $columnKey = $key . '_kosten'; // e.g. wenig_kosten

    // Check if there is at least one price in this column
    $hasPrice = false;
    foreach ($rows as $row) {
      $val = $row[$columnKey] ?? '';
      if (!empty($val) && $val !== '-') {
        $hasPrice = true;
        break;
      }
    }

    if (!$hasPrice) {
      return '';
    }

    ob_start();
  ?>
    <article
      class="card bg-base-100 shadow-lg hover:shadow-2xl hover:-translate-y-1 transition-all duration-300 border-t-4 <?= esc_attr($info['class']) ?> h-full">
      <?php if (!empty($info['badge'])): ?>
        <div class="absolute top-4 right-4">
          <div class="badge badge-sm badge-outline font-semibold <?= esc_attr($info['badge_class']) ?>"><?= $info['badge'] ?></div>
        </div>
      <?php endif; ?>

      <div class="card-body p-6 md:p-8 flex flex-col">
        <h3 class="card-title text-xl md:text-2xl font-bold mb-6 justify-center text-center min-h-[3rem] items-center text-base-content">
          <?= wp_kses_post($title) ?>
        </h3>

        <ul class="space-y-3 flex-1 m-0 p-0 list-none">
          <?php foreach ($rows as $row):
            $serviceName = $row['art_kosten'] ?? '';
            $price = $row[$columnKey] ?? '-';
            if (empty($serviceName))
              continue;
          ?>
            <li class="flex justify-between items-center border-b border-base-200/60 pb-2 last:border-0 hover:bg-base-200/30 px-2 -mx-2 rounded transition-colors">
              <span class="text-base-content/70 text-sm font-medium"><?= esc_html($serviceName) ?></span>
              <span class="font-bold text-primary whitespace-nowrap ml-2"><?= esc_html($this->formatPrice($price)) ?></span>
            </li>
          <?php endforeach; ?>
        </ul>

      </div>
    </article>
  <?php
    return ob_get_clean();
  }

  public function render(array $data, string $bgClass = 'bg-base-100'): string
  {
    // Fallback or full table rendering if needed, 
    // but user asked for CARDS via layouts.
    // If render() is called directly, we check if we should render table.
    // For now, let's keep table render logic here OR return empty if we only want cards via renderManyFromModel.
    // BUT: The template currently calls renderManyFromModel.
    // So I will leave this render() method as the "Legacy Table" implementation 
    // in case they want to switch back, or for other calls.

    if (empty($data)) {
      return '';
    }

    // ... (Existing Table Logic can remain or be replaced. 
    // Since I am replacing the file content, I should probably keep the table logic 
    // or if I replace the whole class, I need to decide.
    // The user instruction "as cards" implies using the method calls I just set up.
    // I will keep render() as the Table version for backward compatibility.)

    // Extract heading from the first item
    $heading = $data[0]['heading'] ?? '';

    ob_start();
  ?>

    <section class="<?= esc_attr($bgClass) ?> py-24 lg:py-32">
      <div class="container mx-auto px-4">
        <?php if ($heading): ?>
          <div class="text-center mb-12">
            <h2 class="text-3xl md:text-4xl font-bold text-primary mb-4">
              <?= $this->renderHeading($heading, 50) ?>
            </h2>
            <div class="divider divider-accent w-24 mx-auto"></div>
          </div>
        <?php endif; ?>

        <!-- DaisyUI Table inside a Card -->
        <div class="card bg-base-100 shadow-xl overflow-hidden border border-base-200">
          <div class="overflow-x-auto">
            <table class="table table-zebra w-full">
              <!-- Head -->
              <thead class="bg-primary text-primary-content text-base">
                <tr>
                  <th class="p-4">Leistung / Größe</th>
                  <th class="p-4">Wenig Hausrat</th>
                  <th class="p-4">Normaler Hausrat</th>
                  <th class="p-4">Viel Hausrat</th>
                  <th class="p-4 font-extrabold">Messie / Extrem</th>
                </tr>
              </thead>
              <!-- Body -->
              <tbody>
                <?php foreach ($data as $index => $row): ?>
                  <tr class="hover">
                    <th class="font-bold text-secondary">
                      <?= esc_html($row['art_kosten']) ?>
                    </th>
                    <td><?= esc_html($this->formatPrice($row['wenig_kosten'] ?? '-')) ?></td>
                    <td><?= esc_html($this->formatPrice($row['normal_kosten'] ?? '-')) ?></td>
                    <td><?= esc_html($this->formatPrice($row['viel_kosten'] ?? '-')) ?></td>
                    <td class="font-semibold text-error">
                      <?= esc_html($this->formatPrice($row['messie_kosten'] ?? '-')) ?>
                    </td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        </div>

        <div class="alert alert-info shadow-lg mt-8 max-w-2xl mx-auto">
          <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
            class="stroke-current flex-shrink-0 w-6 h-6">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
              d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
          </svg>
          <span>* Alle Preise sind Richtwerte und können je nach Aufwand variieren.</span>
        </div>
      </div>
    </section>
<?php
    return ob_get_clean();
  }
}
