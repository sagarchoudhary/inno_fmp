<?php
/**
 * @file
 * Contains \Drupal\example\Form\GlobalSettingsForm
 */
namespace Drupal\site_wide_var\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Configure example settings for this site.
 */
class GlobalSettingsForm extends ConfigFormBase {
  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'site_wide_var_settings';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      'site_wide_var.settings',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('site_wide_var.settings');

    $form['total_hours'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('Total Hours'),
      '#default_value' => $config->get('hours'),
      '#required' => TRUE,
      );
    $form['total_expenditure'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('Total Expenditure'),
      '#default_value' => $config->get('expenditure'),
      '#required' => TRUE,
      );
    $form['total_salary'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('Total Salary'),
      '#default_value' => $config->get('salary'),
      '#required' => TRUE,
    );

    return parent::buildForm($form, $form_state);
  }

 /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    if (!is_numeric($form_state->getValue('total_hours'))) {
      $form_state->setErrorByName('total_hours', $this->t('Total Hours must be numeric'));
    }
    if (!is_numeric($form_state->getValue('total_expenditure'))) {
      $form_state->setErrorByName('total_expenditure', $this->t('Total expenditure must be numeric'));
    }
    if (!is_numeric($form_state->getValue('total_salary'))) {
      $form_state->setErrorByName('total_salary', $this->t('Total Salary must be numeric'));
    }
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $config = \Drupal::service('config.factory')->getEditable('site_wide_var.settings');
    $config->set('hours', $form_state->getValue('total_hours'))
          ->set('expenditure', $form_state->getValue('total_expenditure'))
          ->set('salary', $form_state->getValue('total_salary'))
          ->save();

    parent::submitForm($form, $form_state);
  }
}
