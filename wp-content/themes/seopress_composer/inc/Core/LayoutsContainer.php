<?php

namespace SeopressComposer\Core;

use SeopressComposer\Layouts\OneColumn;
use SeopressComposer\Layouts\TwoColumns;
use SeopressComposer\Layouts\ThreeColumns;
use SeopressComposer\Layouts\FourColumns;
use SeopressComposer\Layouts\FiveColumns;
use SeopressComposer\Layouts\SixColumns;

use SeopressComposer\Components\SectionTitle;

/**
 * LayoutsContainer
 * 
 * Centralized handler for rendering 1, 2, 3, 4, 5, or 6 column layouts.
 */
class LayoutsContainer
{
  private OneColumn $oneColumn;
  private TwoColumns $twoColumns;
  private ThreeColumns $threeColumns;
  private FourColumns $fourColumns;
  private FiveColumns $fiveColumns;
  private SixColumns $sixColumns;

  public function __construct(
    OneColumn $oneColumn,
    TwoColumns $twoColumns,
    ThreeColumns $threeColumns,
    FourColumns $fourColumns,
    FiveColumns $fiveColumns,
    SixColumns $sixColumns
  ) {
    $this->oneColumn = $oneColumn;
    $this->twoColumns = $twoColumns;
    $this->threeColumns = $threeColumns;
    $this->fourColumns = $fourColumns;
    $this->fiveColumns = $fiveColumns;
    $this->sixColumns = $sixColumns;
  }

  /**
   * Render content in specified column layout
   * 
   * @param mixed $content Content string (for 1 col) or array of strings (for 2/3/4/5/6 cols)
   * @param int $columns Number of columns (1-6)
   * @param array $options Layout options ['title', 'description', 'wrapper_class', 'container_class']
   * @return string Rendered HTML
   */
  public function render(mixed $content, int $columns = 1, array $options = []): string
  {
    // Check if content is empty (string or array of strings)
    $isEmpty = is_array($content) ? trim(implode('', $content)) === '' : trim((string) $content) === '';

    if ($isEmpty) {
      return '';
    }

    $output = '';

    switch ($columns) {
      case 2:
        if (!is_array($content)) {
          $content = [$content, ''];
        }
        $chunks = array_chunk($content, 2);
        foreach ($chunks as $index => $chunk) {
          $chunkOptions = $this->getChunkOptions($options, $index);
          $output .= $this->twoColumns->render(
            $chunk[0] ?? '',
            $chunk[1] ?? '',
            $chunkOptions
          );
        }
        return $output;

      case 3:
        if (!is_array($content)) {
          $content = [$content, '', ''];
        }
        $chunks = array_chunk($content, 3);
        foreach ($chunks as $index => $chunk) {
          $chunkOptions = $this->getChunkOptions($options, $index);
          $output .= $this->threeColumns->render(
            $chunk[0] ?? '',
            $chunk[1] ?? '',
            $chunk[2] ?? '',
            $chunkOptions
          );
        }
        return $output;

      case 4:
        if (!is_array($content)) {
          $content = [$content, '', '', ''];
        }
        $chunks = array_chunk($content, 4);
        foreach ($chunks as $index => $chunk) {
          $chunkOptions = $this->getChunkOptions($options, $index);
          $output .= $this->fourColumns->render(
            $chunk[0] ?? '',
            $chunk[1] ?? '',
            $chunk[2] ?? '',
            $chunk[3] ?? '',
            $chunkOptions
          );
        }
        return $output;

      case 5:
        if (!is_array($content)) {
          $content = array_pad([$content], 5, '');
        }
        $chunks = array_chunk($content, 5);
        foreach ($chunks as $index => $chunk) {
          $chunkOptions = $this->getChunkOptions($options, $index);
          $output .= $this->fiveColumns->render(
            $chunk[0] ?? '',
            $chunk[1] ?? '',
            $chunk[2] ?? '',
            $chunk[3] ?? '',
            $chunk[4] ?? '',
            $chunkOptions
          );
        }
        return $output;

      case 6:
        if (!is_array($content)) {
          $content = array_pad([$content], 6, '');
        }
        $chunks = array_chunk($content, 6);
        foreach ($chunks as $index => $chunk) {
          $chunkOptions = $this->getChunkOptions($options, $index);
          $output .= $this->sixColumns->render(
            $chunk[0] ?? '',
            $chunk[1] ?? '',
            $chunk[2] ?? '',
            $chunk[3] ?? '',
            $chunk[4] ?? '',
            $chunk[5] ?? '',
            $chunkOptions
          );
        }
        return $output;

      case 1:
      default:
        $contentString = is_array($content) ? implode('', $content) : (string) $content;
        return $this->oneColumn->render($contentString, $options);
    }
  }

  /**
   * Helper to determine options for a specific chunk.
   * Only the first chunk (index 0) gets the title and description.
   * Subsequent chunks have these removed to prevent duplication.
   */
  private function getChunkOptions(array $options, int $index): array
  {
    $formattedOptions = $options;
    if ($index > 0) {
      unset($formattedOptions['title']);
      unset($formattedOptions['description']);
    }
    return $formattedOptions;
  }
}
