<?php

/**
 * @file
 * Contains \Drupal\csv_data_upload\Form\csvuploaderForm.
 */
namespace Drupal\csv_data_upload\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;


class csvuploaderForm extends FormBase {
  public function getFormId() {
    return 'csvuploader-form';
  }

  public function buildForm(array $form, FormStateInterface $form_state) {
    $form['#attributes'] = array(
    'enctype' => 'multipart/form-data'
    );

    $form['cash_flow_type'] = array(
      '#type' => 'radios',
      '#options' => array(
        'in'   => t('Cash In'),
        'out' => t('Cash Out'),
      ),
      '#title' => t('Cash Flow Type')
    );

    $form['csvfile'] = array(
      '#title' => t('CSV File'),
      '#type'  => 'file',
      '#description' => (T('Upload csv file')),
    );

    $form['submit'] = array(
      '#type' => 'submit',
      '#value' => t('Import Data'),
    );
    return $form;
  }

  public function validateForm(array &$form, FormStateInterface $form_state) {
    $validators = array(
    'file_validate_extensions' => array( 'csv' ),
    );

    if ($file = file_save_upload('csvfile', $validators, "public://csv_import", 0, FILE_EXISTS_RENAME) ) {
      $form_state->setValue('csvuploader', $file->getFileUri());
    }
    else {
      $form_state->setErrorByName('csvfile', $this->t('Only csv file are allowed.'));
    }
  }

  public function submitForm(array &$form, FormStateInterface $form_state) {
    $file = $form_state->getValue('csvuploader');
    $cash_flow_type = $form_state->getValue('cash_flow_type');
    $file_handle = fopen($file, 'r');
    if (empty($file_handle)) {
      return False;
    }
    // Getting csv data in array
    while (!feof($file_handle) ) {
      $file_data[] = fgetcsv($file_handle);
    }
    fclose($file_handle);

    //Arranging csv data in req array form
    foreach ($file_data as $key => $value) {
      if ($key == 0){
        $record['header'] = array_map('strtolower',$value);
      }
      else {
        if (!empty($value)) {
          $record[] = $value;
        }
      }
    }

    $new_record = array();
    foreach ($record as $record_key => $row) {
      foreach ($row as $row_key => $row_value) {
        $new_record[$record_key][$row_key] = trim($row_value);
      }
    }
    if ($cash_flow_type == 'in') {
      if (!(in_array('date', $new_record['header']) && in_array('project', $new_record['header']) &&  in_array('description', $new_record['header'])
      &&  in_array('servicetype', $new_record['header']) && in_array('amount', $new_record['header']))) {
        drupal_set_message(t('Wrong headers of csv file'), 'error');
        return;
      }
    }
    if ($cash_flow_type == 'out') {
      if (!(in_array('date', $new_record['header']) && in_array('project', $new_record['header']) &&  in_array('description', $new_record['header'])
      &&  in_array('category', $new_record['header']) &&  in_array('cashoutcategory', $new_record['header']) &&  in_array('expendtype', $new_record['header']) &&  in_array('amount', $new_record['header']))) {
        drupal_set_message(t('Wrong headers of csv file'), 'error');
        return;
      }
    }

    $header = $new_record['header'];
    unset($new_record['header']);
    $batch = array(
      'operations' => array(
        array('csv_importer', array($new_record, $header, $cash_flow_type)),
      ),
      'finished' => 'finished_csv_importer',
      'title' => t('Processing CSV '),
      'init_message' => t('Starting import.'),
      'progress_message' => t('Processed @current out of @total.'),
      'error_message' => t('Encountered an error.'),
      'file' => drupal_get_path('module', 'csv_data_upload') . '/csv_import_batch.inc',
    );
    batch_set($batch);
  }
}
