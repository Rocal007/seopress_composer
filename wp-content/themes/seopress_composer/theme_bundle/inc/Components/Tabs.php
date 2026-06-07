<?php
namespace SeopressComposer\Components;

/**
 * Tabs Component
 * Enhanced with DaisyUI Tabs component
 */
class Tabs
{
    public function render(array $tabs = []): string
    {
        if (empty($tabs))
            return '';

        // Determine a unique ID for this tab set
        $tabs_id = 'tabs-' . uniqid('', true);

        ob_start();
        ?>
        <div class="w-full">
            <!-- Tab Headers -->
            <div role="tablist" class="tabs tabs-bordered tabs-lg overflow-x-auto flex-nowrap">
                <?php foreach ($tabs as $index => $tab):
                    $is_active = $index === 0;
                    $tab_uuid = $tabs_id . '-tab-' . $index;
                    ?>
                    <input type="radio" name="<?= $tabs_id ?>" role="tab"
                        class="tab peer whitespace-nowrap <?= $is_active ? 'tab-active' : '' ?>"
                        aria-label="<?= esc_attr($tab['title'] ?? 'Tab ' . ($index + 1)) ?>" <?= $is_active ? 'checked' : '' ?> />

                    <div role="tabpanel"
                        class="tab-content bg-base-100 p-6 border-base-300 rounded-box border-t-0 hidden peer-checked:block">
                        <!-- Direct Content (Rendered by Controller) -->
                        <div class="prose max-w-none">
                            <?= $tab['content'] ?? '' ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }
}
