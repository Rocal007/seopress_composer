<?php

namespace SeopressComposer\Components;

use SeopressComposer\Services\IconService;

class HeaderSearch
{
  private IconService $iconService;

  public function __construct(IconService $iconService)
  {
    $this->iconService = $iconService;
  }

  public function render(): string
  {
    $searchIcon = $this->iconService->getIcon('suche_header', ['inner_class' => 'illu text-primary']);
    
    ob_start();
    ?>
    <form role="search" method="get" action="<?= esc_url(home_url('/')) ?>" class="relative hidden md:flex items-center">
        <input type="search" name="s" placeholder="Suchen..." required 
               class="input input-sm input-bordered rounded-full bg-base-100 text-sm focus:outline-none focus:ring-1 focus:border-primary pr-8 w-32 lg:w-48 transition-all duration-300 border-base-300 shadow-sm" 
               aria-label="Suchen" />
        <button type="submit" class="absolute right-1 btn btn-ghost btn-circle btn-xs hover:bg-transparent hover:scale-110 transition-transform duration-300 flex items-center justify-center text-primary" aria-label="Suchen starten" title="Suchen">
            <div class="w-4 h-4 flex items-center justify-center"><?= $searchIcon ?></div>
        </button>
    </form>
    <?php
    return ob_get_clean();
  }
}
