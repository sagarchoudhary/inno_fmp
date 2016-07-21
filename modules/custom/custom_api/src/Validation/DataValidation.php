<?php

namespace Drupal\custom_api\Validation;

use \Drupal\field\Entity\FieldStorageConfig;
use Drupal\Core\Datetime;

Class DataValidation {

  public static function get_field_allowed_value($field_name, $type) {
    $allowed_value_array = FieldStorageConfig::loadByName($type, $field_name)->getSetting('allowed_values');
    return $allowed_value_array;
  }

  //To Change, EntityMangager will remove from new versions
  public static function check_taxonomy_term($term_name, $vocab_name) {
    $tree = \Drupal::entityManager()->getStorage('taxonomy_term')->loadTree($vocab_name, $parent = 0, $max_depth = NULL, $load_entities = FALSE);
    if (empty($tree)) {
      return NULL;
    }
    foreach ($tree as $key => $term_object) {
      $terms[] = ($term_object->name);
    }
    $terms_new = array_map('strtolower', $terms);
    $term_name = strtolower($term_name);
    if (!in_array($term_name, $terms_new)) {
      return FALSE;
    }
    else {
      return TRUE;
    }
  }
  public static function check_importer_date($date) {
    $unix_date = strtotime($date);
    if (empty($unix_date)) {
      return NULL;
    }

    $date_save = date('Y-m-d', $unix_date);
    return $date_save;
  }
}
