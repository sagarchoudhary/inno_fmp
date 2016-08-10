<?php
/**
 * @file
 * Contains \Drupal\amazing_forms\Forms\OutflowForm.
 */

namespace Drupal\graph_report\Forms;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Component\Utility\UrlHelper;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Drupal\Core\Routing\RequestContext;
use \Drupal\field\Entity\FieldStorageConfig;
/**
 * Contribute form.
 */
class OutflowForm extends FormBase {
  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'graph_block_outflow_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {

    $active1 = array(0 => 'project vise', 1 => 'category vise', 2 => 'sub category vise', 3 => 'Expendature vise');
    $form['viewtype'] = array(
    '#type' => 'radios',
    '#title' => t('Select view'),
    '#default_value' => isset($node->active1) ? $node->active1 : 0,
    '#options' => $active1,
    );

    $proj = OutflowForm::getproject();
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

    $category = OutflowForm::get_field_allowed_value('field_ct_cashout_category','node');
    //dpm($category);
    $cat['none'] = "<None>";
    foreach ($category as $value) {
      $cat[$value] = $value;
    }
    $form['category'] = array(
      '#type' => 'select',
      '#title' => t('Category'),
      '#required' => FALSE,
      '#options' => $cat,
    );

    $subcategory = OutflowForm::get_field_allowed_value('field_ct_cashout_sub_category','node');
    //dpm($subcategory);
    $sub['none'] = "<None>";
    foreach ($subcategory as $value) {
      $sub[$value] = $value;
    }
    $form['subcategory'] = array(
      '#type' => 'select',
      '#title' => t('Sub Category'),
      '#required' => FALSE,
      '#options' => $sub,
    );

    $expend = OutflowForm::get_field_allowed_value('field_ct_cashoutflow_expend_type','node');
    //dpm($expend);
    $exp['none'] = "<None>";
    foreach ($expend as $value) {
      $exp[$value] = $value;
    }
    $form['expend'] = array(
      '#type' => 'select',
      '#title' => t('Expendature Type'),
      '#required' => FALSE,
      '#options' => $exp,
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
    $query_category = $form_state->getValues()['category'];
    $query_subcategory = $form_state->getValues()['subcategory'];
    $query_expend = $form_state->getValues()['expend'];
    $query_chartview = $form_state->getValues()['chartview'];
    $query_year = $form_state->getValues()['yearview'];
    $path = "/cashoutflowblock?viewtype=".$query_viewtype."&project=".$query_project."&category=".$query_category."&subcategory=".$query_subcategory."&expend=".$query_expend."&chartview=".$query_chartview."&year=".$query_year;
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

  public static function get_field_allowed_value($field_name, $type) {
    $allowed_value_array = FieldStorageConfig::loadByName($type, $field_name)->getSetting('allowed_values');
    return $allowed_value_array;
  }
}
?>
