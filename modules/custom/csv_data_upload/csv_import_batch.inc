<?php
use \Drupal\node\Entity\Node;
//use Drupal\taxonomy\TermStorage;
use \Drupal\field\Entity\FieldStorageConfig;
use \Drupal\custom_api\Validation\DataValidation;
// use \Drupal\taxonomy;
// use \Drupal\taxonomy\Entity;
function csv_importer($data, $header, $cash_flow_type, $context) {
  //\Drupal::logger('csv_data_upload')->notice('$k');

  if (!isset($context['sandbox']['progress'])) {
    $context['sandbox']['progress'] = 0;
    $context['sandbox']['current_node'] = 0;
    $context['sandbox']['max'] = sizeof($data);
    $context['sandbox']['success'] = 0;
    $context['sandbox']['fail'] = 0;
  }

  // For this, we decide that we can safely process
  // 5 nodes at a time without a timeout.
  $limit = 10;
  $current_node = $context['sandbox']['current_node'];
  $max_node = $context['sandbox']['max'];

  for ($i = $current_node; $i < $max_node; $i++) {
    if ($cash_flow_type == 'in') {
    $result = _save_cash_inflow_node($data[$i], $header);
    }
    if ($cash_flow_type == 'out') {
    $result = _save_cash_outflow_node($data[$i], $header);
    }
    if ($result) {
      $context['sandbox']['success'] = $context['sandbox']['success'] + 1;
    }
    else {
      $context['sandbox']['fail'] = $context['sandbox']['fail'] + 1;
    }
    //$context['message'] = t('Total row processed ' . $i . 'out of ' . $max_node);
    $context['sandbox']['progress']++;


    if ( $i != 0 && ($i%$limit) == 0) {
      $context['sandbox']['current_node'] = $context['sandbox']['progress'];
      break;
    }
  }

  // Inform the batch engine that we are not finished,
  // and provide an estimation of the completion level we reached.
  if ($context['sandbox']['progress'] != $context['sandbox']['max']) {
    $context['finished'] = $context['sandbox']['progress'] / $context['sandbox']['max'];
  }
  $success = !empty($context['sandbox']['success']) ? $context['sandbox']['success'] : 0;
  $fail = !empty($context['sandbox']['fail']) ? $context['sandbox']['fail'] : 0;
  drupal_set_message(t($success . ' Record imported'), $type = 'success');
  if (!empty($fail)) {
    drupal_set_message(t($fail . ' Failed to import'), $type = 'error');
  }
}

function _save_cash_outflow_node($data, $header) {
  $date = isset($data[array_search('date', $header)]) ? $data[array_search('date', $header)] : NULL;
  $project = isset($data[array_search('project', $header)]) ? $data[array_search('project', $header)] : NULL;
  $description = isset($data[array_search('description', $header)]) ? $data[array_search('description', $header)] : NULL;
  $category = isset($data[array_search('category', $header)]) ? $data[array_search('category', $header)] : NULL;
  $cashoutcategory = isset($data[array_search('cashoutcategory', $header)]) ? $data[array_search('cashoutcategory', $header)] : NULL;
  $amount = isset($data[array_search('amount', $header)]) ? $data[array_search('amount', $header)] : NULL;
  $expendtype = isset($data[array_search('expendtype', $header)]) ? $data[array_search('expendtype', $header)] : NULL;

  $final_date = $project_tid = $category_value = $cashoutcategory_value = $expendtype  = NULL;
  //checking  project value
  if (!empty($project)) {
    if (!DataValidation::check_taxonomy_term($project, 'vocab_project')) {
      //todo logging the error.
      return;
    }
    $project_tid_array = taxonomy_term_load_multiple_by_name($project, 'vocab_project');
    $project_tid = ((array_keys($project_tid_array)[0]));
  }

  if (!empty($date)) {
    $final_date = DataValidation::check_importer_date($date);
    if (empty($final_date)) {
      return;
    }
  }

  if (!empty($category)) {
    //checking category value
    $category_allowed_value = DataValidation::get_field_allowed_value('field_ct_cashout_category', 'node');
    $category_value = array_search($category, $category_allowed_value);
    if ($category_value === FALSE) {
      return;
    }
  }

  if (!empty($cashoutcategory)) {
    //checking cashout category value
    $cashoutcategory_allowed_value = DataValidation::get_field_allowed_value('field_ct_cashout_sub_category', 'node');
    $cashoutcategory_value = array_search($cashoutcategory, $cashoutcategory_allowed_value);
    if ($cashoutcategory_value === FALSE) {
      return;
    }
  }

  if (!empty($expendtype)) {
    //checking cashout category value
    $expendtype_allowed_value = DataValidation::get_field_allowed_value('field_ct_cashoutflow_expend_type', 'node');
    $expendtype_value = strtolower($expendtype);
    if (!isset($expendtype_allowed_value[$expendtype_value])) {
      return;
    }
  }
  // $node = Node::create([
  // 'type'  => 'cash_outflow',
  // 'title' => 'Test content',
  // 'field_ct_cashflow_amount' => [
  //   'value' => $amount,
  // ],
  // 'field_ct_cashflow_date' => [
  //   'value' => $final_date,
  // ],
  // 'field_ct_cashflow_project' => [
  //   'target_id' => $project_tid,
  // ],
  // 'field_ct_cashout_category' => [
  //   'value' => $category_value,
  // ],
  // 'field_ct_cashout_sub_category' => [
  //   'value' => $cashoutcategory_value,
  // ],
  // 'field_ct_cashoutflow_expend_type' => [
  //   'value' => $expendtype_value,
  // ],
  // 'body' => [
  //   'value' => $description,
  // ],
  // ]);
  $node_array = array();
  $node_array['type'] = 'cash_outflow';
  $node_array['title'] = 'cash_outflow';
  if (isset($amount)) {
    $node_array['field_ct_cashflow_amount']['value'] = $amount;
  }
  if (isset($final_date)) {
    $node_array['field_ct_cashflow_date']['value'] = $final_date;
  }
  if (isset($project_tid)) {
    $node_array['field_ct_cashflow_project']['target_id'] = $project_tid;
  }
  if (isset($category_value)) {
    $node_array['field_ct_cashout_category']['value'] = $category_value;
  }
  if (isset($cashoutcategory_value)) {
    $node_array['field_ct_cashout_sub_category']['value'] = $cashoutcategory_value;
  }
  if (isset($expendtype_value)) {
    $node_array['field_ct_cashoutflow_expend_type']['value'] = $expendtype_value;
  }
  if (isset($description)) {
    $node_array['body']['value'] = $description;
  }

  $node = Node::create($node_array);
  $node->save();
  return TRUE;
}


function _save_cash_inflow_node($data, $header) {
  $date = isset($data[array_search('date', $header)]) ? $data[array_search('date', $header)] : NULL;
  $project = isset($data[array_search('project', $header)]) ? $data[array_search('project', $header)] : NULL;
  $description = isset($data[array_search('description', $header)]) ? $data[array_search('description', $header)] : NULL;
  $servicetype = isset($data[array_search('servicetype', $header)]) ? $data[array_search('servicetype', $header)] : NULL;
  $amount = isset($data[array_search('amount', $header)]) ? $data[array_search('amount', $header)] : NULL;

  $service_tid = $final_date = $project_tid = NULL;
  //check project value
  if (!empty($project)) {
    if (!DataValidation::check_taxonomy_term($project, 'vocab_project')) {
      //todo logging the error.
      return;
    }
    $project_tid_array = taxonomy_term_load_multiple_by_name($project, 'vocab_project');
    //TODO use devel
    //getting tid for term
    $project_tid = ((array_keys($project_tid_array)[0]));
  }
  if (!empty($date)) {
    $final_date = DataValidation::check_importer_date($date);
    if (empty($final_date)) {
      return;
    }
  }

  if (!empty($servicetype)) {
    $service_tid_array = taxonomy_term_load_multiple_by_name($servicetype, 'vocab_service_type');
    $service_tid = ((array_keys($service_tid_array)[0]));
  }
  $node_array['type'] = 'cash_inflow';
  $node_array['title'] = 'cash_inflow';
  if (isset($amount)) {
    $node_array['field_ct_cashflow_amount']['value'] = $amount;
  }
  if (isset($final_date)) {
    $node_array['field_ct_cashflow_date']['value'] = $final_date;
  }
  if (isset($project_tid)) {
    $node_array['field_ct_cashflow_project']['target_id'] = $project_tid;
  }
  if (isset($service_tid)) {
    $node_array['field_ct_service_type']['target_id'] = $service_tid;
  }
  if (isset($description)) {
    $node_array['body']['value'] = $description;
  }

  $node = Node::create($node_array);
  $node->save();
  return TRUE;
}


