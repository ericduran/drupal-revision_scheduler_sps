<?php

namespace Drupal\revision_scheduler_sps;

use \Drupal\sps\Plugins\Override\NodeDateOverride;

/**
 * Class RevisionSchedulerOverride
 *
 * @package Drupal\revision_scheduler_sps
 */
class RevisionSchedulerOverride extends NodeDateOverride {

  /**
   * @var array
   */
  protected $results = array();

  /**
   * Get a list of Overrides for SPS.
   *
   * @return array
   *   List of overrides.
   */
  public function getOverrides() {
    $select = db_select('revision_scheduler', 'rs')
      ->fields('rs', array('id', 'entity_type', 'entity_id', 'revision_id'))
      ->condition('time_scheduled', $this->timestamp, '<=')
      ->condition('time_scheduled', REQUEST_TIME, '>=')
      ->condition('time_executed', 0)
      ->condition('operation', array(
        'publish',
        'workbench_moderation_to_published',
        'queues_workbench_publish')
      )
      ->orderBy('time_scheduled', 'ASC')
      ->orderBy('revision_id');

    $this->results = $select->execute()->fetchAllAssoc('id', \PDO::FETCH_ASSOC);
    return $this->processOverrides();
  }

  /**
   * Process the results list.
   *
   * @return array
   *   List of overrides key by entity type and ID.
   */
  protected function processOverrides() {
    $list = array();
    foreach ($this->results as $key => $result) {
      $transform = array();
      $transform['id'] = $result['entity_id'];
      $transform['type'] = $result['entity_type'];
      $transform['revision_id'] = $result['revision_id'] == 0 ? NULL : $result['revision_id'];
      $transform['status'] = $result['revision_id'] > 0 ? 1 : 0;
      $list[$transform['type'] . '-' . $result['entity_id']] = $transform;
    }

    return $list;
  }
}
