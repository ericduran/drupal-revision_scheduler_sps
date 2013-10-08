<?php
namespace Drupal\revision_scheduler_sps;

use \Drupal\sps\Plugins\Override\NodeDateOverride;

class RevisionSchedulerOverride extends NodeDateOverride {

  protected $results = array();

  /**
   * Returns a list of vid's to override the default vids to load.
   *
   * @return
   *  An array of override vids.
   */
  public function getOverrides() {
    $select = db_select('revision_scheduler', 'rs')
      ->fields('rs', array('id', 'entity_type', 'entity_id', 'revision_id'))
      ->condition('time_scheduled', $this->timestamp, '<=')
      ->orderBy('time_scheduled', 'ASC')
      ->orderBy('revision_id');

    $this->results = $select->execute()->fetchAllAssoc('id', \PDO::FETCH_ASSOC);
    return $this->processOverrides();
  }

  protected function processOverrides() {
    $list = array();
    foreach($this->results as $key => $result) {
      $transform = array();
      $transform['id'] = $result['entity_id'];
      $transform['type'] = 'node';
      $transform['revision_id'] = $result['revision_id'] == 0 ? NULL : $result['revision_id'];
      $transform['status'] = $result['revision_id'] > 0 ? 1 : 0;
      $list['node-' . $result['entity_id']] = $transform;
    }
    return $list;
  }
}
