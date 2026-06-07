<?php
namespace SeopressComposer\Components;

/**
 * ProcessPie Component
 * Displays items in a circular pie-chart layout logic.
 * Inspired by the "gezwanzig" wheel diagram.
 */
class ProcessPie
{
  public function render(array $data): string
  {
    if (empty($data['items'])) {
      return '';
    }

    $items = array_values($data['items']); // Ensure 0-indexed
    $count = count($items);
    // Limit to 6 for this specific design to keep the geometry clean
    $items = array_slice($items, 0, 6);

    $center_text = $data['center_text'] ?? get_bloginfo('name');

    // Fallback if less than 6 items? Layout works best with 6.
    // We will assume 6 slots. If fewer items, empty slots or bigger slices?
    // For this specific design request (6 slices), we stick to 6 slots geometry.
    // Angles for 6 slices:
    // Slot 1: Top-Right (30deg to 90deg? No, looks like 12-2 is one slice?)
    // Image shows Vertical line is a divider.
    // So 12:00 is a divider.
    // Slice 1: 0 to 60 deg (Top Right / 1 o'clock)
    // Slice 2: 60 to 120 deg (Right / 3 o'clock)
    // Slice 3: 120 to 180 deg (Bottom Right / 5 o'clock)
    // Slice 4: 180 to 240 deg (Bottom Left / 7 o'clock)
    // Slice 5: 240 to 300 deg (Left / 9 o'clock)
    // Slice 6: 300 to 360 deg (Top Left / 11 o'clock)

    // Positions for text content (approximate % from top/left)
    // We use a predefined map for 6 items to ensure perfect placement
    $positions = [
      0 => ['top' => '15%', 'left' => '65%', 'align' => 'text-left'],   // Top Right
      1 => ['top' => '50%', 'left' => '80%', 'align' => 'text-left'],   // Right
      2 => ['top' => '85%', 'left' => '65%', 'align' => 'text-left'],   // Bottom Right
      3 => ['top' => '85%', 'left' => '35%', 'align' => 'text-right'],  // Bottom Left
      4 => ['top' => '50%', 'left' => '20%', 'align' => 'text-right'],  // Left
      5 => ['top' => '15%', 'left' => '35%', 'align' => 'text-right'],  // Top Left
    ];

    ob_start();
    ?>
    <section class="py-24 lg:py-32 bg-base-100 overflow-hidden">
      <div class="container mx-auto px-4">

        <!-- Mobile Layout (Stack) -->
        <div class="lg:hidden space-y-6">
          <div class="text-center mb-8">
            <div
              class="inline-flex items-center justify-center w-32 h-32 rounded-full bg-white border-4 border-black text-black font-bold text-xl shadow-lg">
              <?= esc_html($center_text) ?>
            </div>
          </div>

          <?php foreach ($items as $item): ?>
            <div class="bg-black text-white p-6 rounded-2xl shadow-xl text-center">
              <h3 class="font-bold text-lg mb-2"><?= esc_html($item['heading'] ?? '') ?></h3>
              <div class="text-sm opacity-80 leading-relaxed"><?= $item['content'] ?? '' ?></div>
            </div>
          <?php endforeach; ?>
        </div>

        <!-- Desktop Layout (Wheel) -->
        <div class="hidden lg:block relative w-[700px] h-[700px] mx-auto">

          <!-- Main Circle Background -->
          <div class="absolute inset-0 rounded-full bg-black shadow-2xl overflow-hidden border-4 border-black">

            <!-- Dividers (White Lines) -->
            <!-- Horizontal -->
            <div class="absolute top-1/2 left-0 w-full h-[2px] bg-white -translate-y-1/2 z-0"></div>
            <!-- Diagonal 1 (60deg) -->
            <div class="absolute top-0 left-1/2 w-[2px] h-full bg-white -translate-x-1/2 rotate-60 z-0 origin-center"></div>
            <!-- Diagonal 2 (-60deg) -->
            <div class="absolute top-0 left-1/2 w-[2px] h-full bg-white -translate-x-1/2 -rotate-60 z-0 origin-center">
            </div>
          </div>

          <!-- Central Hub -->
          <div
            class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-48 h-48 bg-white rounded-full z-20 flex items-center justify-center shadow-lg px-4 text-center">
            <span class="font-bold text-black text-xl leading-tight"><?= esc_html($center_text) ?></span>
          </div>

          <!-- Content Items -->
          <!-- 
                Mapping logic:
                Item 0: Top Right (Slice 1)
                Item 1: Right Middle? (Slice 2)
                But wait, the image layout:
                Top Right: "Markenpositionierung"
                Bottom Right: "Erweiterte Markenkommunikation" ? No, that's Slice 3.
                
                Let's simplify. We place content absolutely based on polar coordinates visually.
             -->
          <?php
          foreach ($items as $i => $item):
            // Determine position based on index (0-5)
            // 0: Top Right (Clock 1-2)
            // 1: Right (Clock 3-4)
            // 2: Bottom Right (Clock 5-6)
            // 3: Bottom Left (Clock 7-8)
            // 4: Left (Clock 9-10)
            // 5: Top Left (Clock 11-12)
      
            // Helper to center the content within the slice
            // We'll use fixed percentages that land in the "meat" of the slice
      
            // Fine-tuned positions for 700x700 circle
            $pos_styles = [
              0 => 'top: 15%; left: 55%; width: 35%; text-align: left;',
              1 => 'top: 42%; left: 68%; width: 28%; text-align: left;', // Reduced width near edge
              2 => 'top: 70%; left: 55%; width: 35%; text-align: left;',
              3 => 'top: 70%; left: 10%; width: 35%; text-align: right;',
              4 => 'top: 42%; left: 4%; width: 28%; text-align: right;',
              5 => 'top: 15%; left: 10%; width: 35%; text-align: right;',
            ];

            $style = $pos_styles[$i] ?? 'display: none;';
            ?>
            <div class="absolute z-10 flex flex-col justify-center pointer-events-none" style="<?= $style ?>">
              <h3 class="text-white font-bold text-lg mb-2 leading-tight"><?= esc_html($item['heading'] ?? '') ?></h3>
              <div class="text-white/80 text-xs leading-relaxed max-w-xs">
                <?= $item['content'] ?? '' ?>
              </div>
            </div>
          <?php endforeach; ?>

        </div>

      </div>
    </section>
    <?php
    return ob_get_clean();
  }
}
