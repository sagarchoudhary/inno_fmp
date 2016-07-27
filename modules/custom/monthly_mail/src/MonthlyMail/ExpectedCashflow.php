<?php

namespace Drupal\monthly_mail\MonthlyMail;

use Drupal\Core\Database\Database;
use Drupal\node\Entity\Node;
Class ExpectedCashflow {

  public static function get_expected_monthly_cashflow($type)  {
    $connection = Database::getConnection();
    $sth = $connection->select('node', 'n');
    $sth->fields('n', array('nid'));
    $sth->condition('n.type', $type, '=');

    $sth->join('node_field_data','node_data', 'n.nid = node_data.nid');
    $sth->condition('node_data.status', '1', '=');

    $sth->join('node__field_ct_cashflow_date','date', 'n.nid = date.entity_id');
    $sth->fields('date', array('field_ct_cashflow_date_value'));

    $sth->join('node__field_ct_cashflow_amount','amount', 'n.nid = amount.entity_id');
    $sth->fields('amount', array('field_ct_cashflow_amount_value'));

    $executed = $sth->execute();
    $cashflow_array = $executed->fetchAll();
    if (empty($cashflow_array)) {
      return NULL;
    }
    $cash_flow_pre_data = array();
    foreach ($cashflow_array as $cashflow_array_key => $cashflow_data) {
      $month = Self::get_month_from_date($cashflow_data->field_ct_cashflow_date_value);
      if (empty($month)) {
        continue;
      }
      $cash_flow_pre_data[$month]['amount'] = isset($cash_flow_pre_data[$month]['amount']) ?
        $cash_flow_pre_data[$month]['amount'] + $cashflow_data->field_ct_cashflow_amount_value : $cashflow_data->field_ct_cashflow_amount_value;
    }
    $total_cashflow = 0;
    foreach ($cash_flow_pre_data as $month_value => $month_amount) {
      $total_cashflow = $total_cashflow + $month_amount['amount'];
    }
    $total_month = count($cash_flow_pre_data);
    $expected_cashflow = $total_cashflow / $total_month;
    return round($expected_cashflow);
  }

  public static function get_month_from_date($date) {
    $data_array = explode('-', $date);
    if (count($data_array) != 3) {
      return NULL;
    }
    return $data_array[1];
  }
}
