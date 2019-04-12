<?php

namespace Drupal\myroute_noindex\Entity;

use Drupal\myroute_noindex\MyrouteNoindexInterface;
use Drupal\Core\Cache\Cache;
use Drupal\Core\Condition\ConditionPluginCollection;
use Drupal\Core\Config\Entity\ConfigEntityBase;

/**
 * Defines the MyrouteNoindex entity.
 *
 * @ConfigEntityType(
 *   id = "myroute_noindex",
 *   label = @Translation("MyrouteNoindex"),
 *   handlers = {
 *     "list_builder" = "Drupal\myroute_noindex\Controller\MyrouteNoindexListBuilder",
 *     "form" = {
 *       "add" =    "Drupal\myroute_noindex\Form\MyrouteNoindexForm",
 *       "edit" =   "Drupal\myroute_noindex\Form\MyrouteNoindexForm",
 *       "delete" = "Drupal\myroute_noindex\Form\MyrouteNoindexDeleteForm"
 *     }
 *   },
 *   config_prefix = "myroute_noindex",
 *   admin_permission = "administer site configuration",
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "label",
 *     "weight" = "weight",
 *     "uuid" = "uuid"
 *   },
 *   config_export = {
 *     "id",
 *     "label",
 *     "route_name",
 *     "items",
 *     "logic",
 *     "conditions",
 *     "weight",
 *   },
 *   links = {
 *     "canonical" =   "/admin/seo/myroute_noindex/list/{myroute_noindex}",
 *     "edit-form" =   "/admin/seo/myroute_noindex/list/{myroute_noindex}/edit",
 *     "delete-form" = "/admin/seo/myroute_noindex/list/{myroute_noindex}/delete",
 *     "collection" =  "/admin/seo/myroute_noindex/list"
 *   }
 * )
 */
class MyrouteNoindex extends ConfigEntityBase implements MyrouteNoindexInterface {
  /**
   * The MyrouteNoindex ID.
   *
   * @var string
   */
  protected $id;


  /**
   * The MyrouteNoindex label.
   *
   * @var string
   */
  protected $label;


  /**
   * @var string
   */
  protected $route_name;


  /**
   * The weight.
   *
   * @var int
   */
  protected $weight = 0;


  /**
   * @var array
   */
  protected $items = [];


  /**
   * The configuration of conditions.
   *
   * @var array
   */
  protected $conditions = [];

  /**
   * Tracks the logic used to compute, either 'and' or 'or'.
   *
   * @var string
   */
  protected $logic = 'and';


  /**
   * The plugin collection that holds the conditions.
   *
   * @var \Drupal\Component\Plugin\LazyPluginCollection
   */
  protected $conditionCollection;


  /**
   * {@inheritdoc}
   */
  public function getPluginCollections() {
    return [
      'conditions' => $this->getConditions(),
    ];
  }


  /**
   * {@inheritdoc}
   */
  public function getRouteName() {
    return $this->route_name;
  }

  /**
   * {@inheritdoc}
   */
  public function setRouteName($route_name) {
    $this->route_name = $route_name;
    return $this;
  }


  /**
   * {@inheritdoc}
   */
  public function getWeight() {
    return $this->weight;
  }

  /**
   * {@inheritdoc}
   */
  public function setWeight($weight) {
    $this->weight = $weight;
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getItems() {
    return $this->items;
  }

  /**
   * {@inheritdoc}
   */
  public function setItems($items) {
    $this->items = $items;
    return $this;
  }


  /**
   * {@inheritdoc}
   */
  public function getLogic() {
    return $this->logic;
  }

  /**
   * {@inheritdoc}
   */
  public function setLogic($logic) {
    $this->logic = $logic;
    return $this;
  }


  /**
   * {@inheritdoc}
   */
  public function getConditions() {
    if (!$this->conditionCollection) {
      $this->conditionCollection = new ConditionPluginCollection(\Drupal::service('plugin.manager.condition'), $this->get('conditions'));
    }
    return $this->conditionCollection;
  }

  /**
   * {@inheritdoc}
   */
  public function getCondition($condition_id) {
    return $this->getConditions()->get($condition_id);
  }

  /**
   * {@inheritdoc}
   */
  public function addCondition($configuration) {
    $configuration['uuid'] = $this->uuidGenerator()->generate();
    $this->getConditions()
      ->addInstanceId($configuration['uuid'], $configuration);
    return $configuration['uuid'];
  }

  /**
   * {@inheritdoc}
   */
  public function removeCondition($condition_id) {
    $this->getConditions()->removeInstanceId($condition_id);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheTags() {
    $tags = parent::getCacheTags();
    return Cache::mergeTags($tags, ['myroute_noindex:' . $this->id]);
  }

}
