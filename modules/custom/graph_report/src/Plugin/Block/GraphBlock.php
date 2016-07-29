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
  // $x_axis = array('2014', '2015');
  // $series = array('2014' => array('sis' => array(12,34,35), 'ebnl' => array(23,45,32)), '2015' => array('sis' => array(12,34,35), 'ebnl' => array(23,45,32)));
  // $project = array('ebnl','sis');
  // $var_pass[] =
  $var = 'test'
  return array(
  'type' => 'markup',
  '#markup' => '<div id="container" style="min-width: 310px; height: 400px; max-width: 600px; margin: 0 auto"></div>',
  '#attached' => array(
    'library' =>  array(
      'graph_report/mygraphjs',
    ),
    'drupalSettings' => array('graph_report' => array('mygraphjs' => array('project_name' => $var))),
    ),
   );
  }
}
