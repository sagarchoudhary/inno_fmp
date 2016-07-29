<?php
/**
 * @file
 */
namespace Drupal\graph_report\Plugin\Block;

use Drupal\Core\Block\BlockBase;
/**
 * Provides Graph block.
 *
 * @Block(
 *   id = "graphblock",
 *   admin_label = @Translation("Graph Block"),
 *   category = @Translation("Blocks")
 * )
 */
class GraphBlock extends BlockBase {

/**
   * {@inheritdoc}
   */
  public function build() {
     return [
    '#theme' => 'mygraph',
    '#project' => ['SIS','SNU', 'EBNL'],
    '#project_data' => ['SIS' => [12,23,11],'SNU' => [23,12,31], 'EBNL' => [14,15,16]],
  ];
  }
}
