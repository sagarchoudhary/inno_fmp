<?php
/**
 * @file
 * Contains \Drupal\amazing_forms\Form\FilterForm.
 */

namespace Drupal\graph_report\Forms;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Component\Utility\UrlHelper;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Drupal\Core\Routing\RequestContext;
/**
 * Contribute form.
 */
class FilterForm extends FormBase {
  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'graph_block_filter_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {

    $active1 = array(0 => 'project vise', 1 => 'service vise');
    $form['viewtype'] = array(
    '#type' => 'radios',
    '#title' => t('Select view'),
    '#default_value' => isset($node->active1) ? $node->active1 : 0,
    '#options' => $active1,
    );

    $proj = FilterForm::getproject();
    $active['none'] = "<None>";
    foreach ($proj as $key => $value) {
      $active[$value->getName()] = $value->getName();
    }
    $form['project'] = array(
      '#type' => 'select',
      '#title' => t('Project'),
      '#required' => FALSE,
      '#options' => $active,
    );

    $service = FilterForm::getservicetype();
    $sre['none'] = "<None>";
    foreach ($service as $key => $value) {
      $sre[$value->getName()] = $value->getName();
    }
    $form['service'] = array(
      '#type' => 'select',
      '#title' => t('Service Type'),
      '#required' => FALSE,
      '#options' => $sre,
    );

    $chart = array('none' => '<None>', 'month' => 'Month Vise', 'year' => 'Year Vise');
    $form['chartview'] = array(
      '#type' => 'select',
      '#title' => t('Chart View Type'),
      '#required' => FALSE,
      '#options' => $chart,
    );

    $year = array('none' => '<None>','2015' => '2015', '2016' => '2016','2017' => '2017','2018' => '2018','2019' => '2019', '2020' => '2020');
    $form['yearview'] = array(
      '#type' => 'select',
      '#title' => t('Select Year'),
      '#required' => FALSE,
      '#options' => $year,
      '#states' => array(
        'visible' => array(   // action to take.
        ':input[name="chartview"]' => array('value' => 'month'),
        ),
      ),
    );


    $form['submit'] = array(
      '#type' => 'submit',
      '#value' => t('Apply'),
    );

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $query_viewtype = $form_state->getValues()['viewtype'];
    $query_project = $form_state->getValues()['project'];
    $query_service = $form_state->getValues()['service'];
    $query_chartview = $form_state->getValues()['chartview'];
    $query_year = $form_state->getValues()['yearview'];
    foreach ($form_state->getValues() as $key => $value) {
      drupal_set_message($key . ': ' . $value);
    }

    $path = "/cashinflowblock?viewtype=".$query_viewtype."&project=".$query_project."&service=".$query_service."&chartview=".$query_chartview."&year=".$query_year;
    //dpm($path);
    $response = new RedirectResponse($path);
    $response->send();
  }

  public function getproject() {
    $query = \Drupal::entityQuery('taxonomy_term');
    $query->condition('vid', "vocab_project");
    $tids = $query->execute();
    $terms = \Drupal\taxonomy\Entity\Term::loadMultiple($tids);
    return $terms;
  }

  public function getservicetype() {
    $query = \Drupal::entityQuery('taxonomy_term');
    $query->condition('vid', "vocab_service_type");
    $tids = $query->execute();
    $terms = \Drupal\taxonomy\Entity\Term::loadMultiple($tids);
    return $terms;
  }
}
?>
