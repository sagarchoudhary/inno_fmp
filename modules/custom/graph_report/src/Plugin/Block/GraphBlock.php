<?php
/**
 * @file
 */
namespace Drupal\graph_report\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Block\BlockPluginInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\graph_report\Forms\FilterForm;
/**
 * Provides Graph block.
 *
 * @Block(
 *   id = "graphblock",
 *   admin_label = @Translation("Graph Block"),
 *   category = @Translation("Blocks")
 * )
 */
class GraphBlock extends BlockBase implements BlockPluginInterface {

/**
   * {@inheritdoc}
*/
public function build() {

  $viewtype = isset($_GET['viewtype'])?$_GET['viewtype']:0;
  $project = isset($_GET['project'])?$_GET['project']:'none';
  $service = isset($_GET['service'])?$_GET['service']:'none';
  $chartview = isset($_GET['chartview'])?$_GET['chartview']:'none';
  $yearselect = isset($_GET['year'])?$_GET['year']:'none';

  $x_year = array('2015' => '2015','2016' => '2016', '2017' => '2017', '2018' => '2018', '2019' => '2019', '2020' => '2020');
  $x_month = array('01' => 'jan','02' => 'feb','03' => 'mar','04' => 'apr','05' => 'may','06' => 'jun','07' => 'jul','08' => 'aug','09' => 'sep','10' => 'oct','11' => 'nov','12' => 'dec');

  $project_terms = FilterForm::getproject();
  foreach ($project_terms as $key => $value) {
    $project_list[$value->id()] = $value->getName();
    if ($value->getName() == $project) {
      $project_key = $value->id();
    }
  }
  //dpm($project_list);

  $service_terms = FilterForm::getservicetype();
  foreach ($service_terms as $key => $value) {
    $service_list[$value->id()] = $value->getName();
    if ($value->getName() == $service) {
      $service_key = $value->id();
    }
  }
  //dpm($service_list);

  //$yearview = isset($_GET['yearview'])?$_GET['yearview']:'none';

  $query = \Drupal::entityQuery('node')
              ->condition('type','cash_inflow');
    if ($project != 'none') {
      $query = $query->condition('field_ct_cashflow_project.entity.name', $project);
      $project_array[$project_key] = $project;
    }
    else {
      $project_array = $project_list;
    }
    if ($service != 'none') {
      $query = $query->condition('field_ct_service_type.entity.name', $service);
      $service_array[$service_key] = $service;
    }
    else {
      $service_array = $service_list;
    }
  $nids = $query->execute();
  //dpm($result);
  $nodes = entity_load_multiple('node', $nids);
  //dpm($nodes);
  if($viewtype == 0) {

    foreach($project_array as $key => $value) {
      $ndata[$value] = array();
    }

    foreach ($nodes as $key => $value) {
      $node = $value->toArray();
      $amounts = $node['field_ct_cashflow_amount'][0]['value'];
      //dpm($amounts);
      $pro = $node['field_ct_cashflow_project'][0]['target_id'];
      $date = $node['field_ct_cashflow_date'][0]['value'];
      //dpm($date);
      $date_array = explode("-",$date);
      $year = $date_array[0];
      $month = $date_array[1];
      $day = $date_array[2];
      //dpm($date_array);
      $year_view[$year][$pro] += $amounts;
      $month_view[$year][$pro][$month] += $amounts;
      //dpm($year_view);

      //array_push($ndata[$project_array[$pro]],(int)$amounts);
      //dpm($pro);
      //dpm($amounts[0]['value']);

    }

    foreach ($project_array as $key => $value) {
      if ($chartview == 'year') {
        $ntitle = "Yearly Cash Flow";
        foreach ($x_year as $key1 => $value1) {
          $naxis[] = $value1;
          if(isset($year_view[$key1][$key])) {
            array_push($ndata[$value],$year_view[$key1][$key]);
          }
          else {
            array_push($ndata[$value],0);
          }
        }
      }
      if ($chartview == 'month' || $chartview == 'none') {
        $ntitle = "Monthly Cash Flow";
        if($yearselect != 'none') {
          foreach ($x_month as $key1 => $value1) {
            $naxis[] = $value1;
            if(isset($month_view[$yearselect][$key][$key1])) {
              array_push($ndata[$value],$month_view[$yearselect][$key][$key1]);
            }
            else {
              array_push($ndata[$value],0);
            }
          }
        }
        else {
          $currentyear = date("Y");
          foreach ($x_month as $key1 => $value1) {
            $naxis[] = $value1;
            if(isset($month_view[$currentyear][$key][$key1])) {
              array_push($ndata[$value],$month_view[$currentyear][$key][$key1]);
            }
            else {
              array_push($ndata[$value],0);
            }
          }
        }
      }
    }
  }
  else {

    foreach($service_array as $key => $value) {
      $ndata[$value] = array();
    }

    foreach ($nodes as $key => $value) {
      $node = $value->toArray();
      $amounts = $node['field_ct_cashflow_amount'][0]['value'];
      //dpm($amounts);
      $serv = $node['field_ct_service_type'][0]['target_id'];
      $date = $node['field_ct_cashflow_date'][0]['value'];
      //dpm($date);
      $date_array = explode("-",$date);
      $year = $date_array[0];
      $month = $date_array[1];
      $day = $date_array[2];
      //dpm($date_array);
      $year_view[$year][$serv] += $amounts;
      $month_view[$year][$serv][$month] += $amounts;
      //dpm($year_view);

      //array_push($ndata[$project_array[$pro]],(int)$amounts);
      //dpm($pro);
      //dpm($amounts[0]['value']);

    }

    foreach ($service_array as $key => $value) {
      if ($chartview == 'year') {
        $ntitle = "Yearly Cash Flow";
        foreach ($x_year as $key1 => $value1) {
          $naxis[] = $value1;
          if(isset($year_view[$key1][$key])) {
            array_push($ndata[$value],$year_view[$key1][$key]);
          }
          else {
            array_push($ndata[$value],0);
          }
        }
      }
      if ($chartview == 'month' || $chartview == 'none') {
        $ntitle = "Monthly Cash Flow";
        if($yearselect != 'none') {
          foreach ($x_month as $key1 => $value1) {
            $naxis[] = $value1;
            if(isset($month_view[$yearselect][$key][$key1])) {
              array_push($ndata[$value],$month_view[$yearselect][$key][$key1]);
            }
            else {
              array_push($ndata[$value],0);
            }
          }
        }
        else {
          $currentyear = date("Y");
          foreach ($x_month as $key1 => $value1) {
            $naxis[] = $value1;
            if(isset($month_view[$currentyear][$key][$key1])) {
              array_push($ndata[$value],$month_view[$currentyear][$key][$key1]);
            }
            else {
              array_push($ndata[$value],0);
            }
          }
        }
      }
    }
  }

  //dpm($ndata);
  $y_axis = 'On y Axis';
  //$series = array('sis' => array(12,34,35), 'ebnl' => array(23,45,32));
  $series = $ndata;
  $title = $ntitle;
  $subtitle = "Graph income";
  // dpm($_GET['project']);
  $x_axis = $naxis;
  $view = array(
  'type' => 'markup',
  '#markup' => '<div id="container" style="min-width: 310px; height: 400px; max-width: 600px; margin: 0 auto"></div>',
  '#attached' => array(
    'library' =>  array(
      'graph_report/mygraphjs',
    ),
    'drupalSettings' => array('graph_report' => array('mygraphjs' => array('title' => $title,'subtitle' => $subtitle,'xAxis' => $x_axis ,'yAxis' => $y_axis ,'series' => $series))),
    ),
   );

  $form = \Drupal::formBuilder()->getForm('Drupal\graph_report\Forms\FilterForm');
  $com = array();
  $com['form'] = $form;
  $com['graph'] = $view;
  return $com;
  }

  /**
   * {@inheritdoc}
   */
  // public function blockForm($form, FormStateInterface $form_state) {
  //   $form = parent::blockForm($form, $form_state);

  //   $config = $this->getConfiguration();

  //   $form['hello_block_settings'] = array (
  //     '#type' => 'textfield',
  //     '#title' => $this->t('Who'),
  //     '#description' => $this->t('Who do you want to say hello to?'),
  //     '#default_value' => isset($config['hello_block_settings']) ? $config['hello_block_settings'] : ''
  //   );


  //   return $form;
  // }
}
