<?php
/**
 * @file
 */
namespace Drupal\graph_report\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Block\BlockPluginInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\graph_report\Forms\OutflowForm;
/**
 * Provides Graph block.
 *
 * @Block(
 *   id = "outflowblock",
 *   admin_label = @Translation("Outflow Block"),
 *   category = @Translation("Blocks")
 * )
 */
class OutflowBlock extends BlockBase implements BlockPluginInterface {

/**
   * {@inheritdoc}
*/
public function build() {

  $viewtype = isset($_GET['viewtype'])?$_GET['viewtype']:0;
  $project = isset($_GET['project'])?$_GET['project']:'none';
  $category = isset($_GET['category'])?$_GET['category']:'none';
  $subcategory = isset($_GET['subcategory'])?$_GET['subcategory']:'none';
  $expend = isset($_GET['expend'])?$_GET['expend']:'none';
  $chartview = isset($_GET['chartview'])?$_GET['chartview']:'none';
  $yearselect = isset($_GET['year'])?$_GET['year']:'none';

  $x_year = array('2015' => '2015','2016' => '2016', '2017' => '2017', '2018' => '2018', '2019' => '2019', '2020' => '2020');
  $x_month = array('01' => 'jan','02' => 'feb','03' => 'mar','04' => 'apr','05' => 'may','06' => 'jun','07' => 'jul','08' => 'aug','09' => 'sep','10' => 'oct','11' => 'nov','12' => 'dec');

  $project_terms = OutflowForm::getproject();
  foreach ($project_terms as $key => $value) {
    $project_list[$value->id()] = $value->getName();
    if ($value->getName() == $project) {
      $project_key = $value->id();
    }
  }

  $category_terms = OutflowForm::get_field_allowed_value('field_ct_cashout_category','node');
  $subcategory_terms = OutflowForm::get_field_allowed_value('field_ct_cashout_sub_category','node');
  $expend_terms = OutflowForm::get_field_allowed_value('field_ct_cashoutflow_expend_type','node');

    $query = \Drupal::entityQuery('node')
              ->condition('type','cash_outflow');
    if ($project != 'none') {
      $query = $query->condition('field_ct_cashflow_project.entity.name', $project);
      $project_array[$project_key] = $project;
    }
    else {
      $project_array = $project_list;
    }
    if ($category != 'none') {
      $query = $query->condition('field_ct_cashout_category', array_search ($category, $category_terms));
      $category_array[array_search ($category, $category_terms)] = $category;
    }
    else {
      $category_array = $category_terms;
    }
    if ($subcategory != 'none') {
      $query = $query->condition('field_ct_cashout_sub_category', array_search ($subcategory, $subcategory_terms));
      $subcategory_array[array_search ($subcategory, $subcategory_terms)] = $subcategory;
    }
    else {
      $subcategory_array = $subcategory_terms;
    }
    if ($expend != 'none') {
      $query = $query->condition('field_ct_cashoutflow_expend_type', array_search ($expend, $expend_terms));
      $expend_array[array_search ($expend, $expend_terms)] = $expend;
    }
    else {
      $expend_array = $expend_terms;
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
  else if($viewtype == 1) {

    foreach($category_array as $key => $value) {
      $ndata[$value] = array();
    }

    foreach ($nodes as $key => $value) {
      $node = $value->toArray();
      $amounts = $node['field_ct_cashflow_amount'][0]['value'];
      //dpm($amounts);
      $catg = $node['field_ct_cashout_category'][0]['value'];
      $date = $node['field_ct_cashflow_date'][0]['value'];
      //dpm($date);
      $date_array = explode("-",$date);
      $year = $date_array[0];
      $month = $date_array[1];
      $day = $date_array[2];
      //dpm($date_array);
      $year_view[$year][$catg] += $amounts;
      $month_view[$year][$catg][$month] += $amounts;
      //dpm($year_view);

      //array_push($ndata[$project_array[$pro]],(int)$amounts);
      //dpm($pro);
      //dpm($amounts[0]['value']);

    }

    foreach ($category_array as $key => $value) {
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
  else if($viewtype == 2) {

    foreach($subcategory_array as $key => $value) {
      $ndata[$value] = array();
    }

    foreach ($nodes as $key => $value) {
      $node = $value->toArray();
      $amounts = $node['field_ct_cashflow_amount'][0]['value'];
      //dpm($amounts);
      $subcatg = $node['field_ct_cashout_sub_category'][0]['value'];
      $date = $node['field_ct_cashflow_date'][0]['value'];
      //dpm($date);
      $date_array = explode("-",$date);
      $year = $date_array[0];
      $month = $date_array[1];
      $day = $date_array[2];
      //dpm($date_array);
      $year_view[$year][$subcatg] += $amounts;
      $month_view[$year][$subcatg][$month] += $amounts;
      //dpm($year_view);

      //array_push($ndata[$project_array[$pro]],(int)$amounts);
      //dpm($pro);
      //dpm($amounts[0]['value']);

    }

    foreach ($subcategory_array as $key => $value) {
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

    foreach($expend_array as $key => $value) {
      $ndata[$value] = array();
    }

    foreach ($nodes as $key => $value) {
      $node = $value->toArray();
      $amounts = $node['field_ct_cashflow_amount'][0]['value'];
      //dpm($amounts);
      $exp = $node['field_ct_cashoutflow_expend_type'][0]['value'];
      $date = $node['field_ct_cashflow_date'][0]['value'];
      //dpm($date);
      $date_array = explode("-",$date);
      $year = $date_array[0];
      $month = $date_array[1];
      $day = $date_array[2];
      //dpm($date_array);
      $year_view[$year][$exp] += $amounts;
      $month_view[$year][$exp][$month] += $amounts;
      //dpm($year_view);

      //array_push($ndata[$project_array[$pro]],(int)$amounts);
      //dpm($pro);
      //dpm($amounts[0]['value']);

    }

    foreach ($expend_array as $key => $value) {
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

  $form = \Drupal::formBuilder()->getForm('Drupal\graph_report\Forms\OutflowForm');
  $com = array();
  $com['form'] = $form;
  $com['graph'] = $view;
  return $com;
  }


}
