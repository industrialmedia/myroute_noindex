<?php

namespace Drupal\myroute_noindex;

use Drupal\Core\Config\Entity\ConfigEntityInterface;
use Drupal\Core\Entity\EntityWithPluginCollectionInterface;

/**
 * Provides an interface for defining MyrouteNoindex entities.
 */
interface MyrouteNoindexInterface extends ConfigEntityInterface, EntityWithPluginCollectionInterface {


  /**
   * Gets the route_name.
   *
   * @return string
   *   route_name of the MyrouteNoindex.
   */
  public function getRouteName();


  /**
   * Sets the MyrouteNoindex route_name.
   *
   * @param string $route_name
   *   The MyrouteNoindex route_name.
   *
   * @return \Drupal\myroute_noindex\MyrouteNoindexInterface
   *   The called MyrouteNoindex entity.
   */
  public function setRouteName($route_name);


  /**
   * Gets the weight.
   *
   * @return int
   *   weight of the MyrouteNoindex.
   */
  public function getWeight();

  /**
   * Sets the MyrouteNoindex weight.
   *
   * @param int $weight
   *   The MyrouteNoindex weight.
   *
   * @return \Drupal\myroute_noindex\MyrouteNoindexInterface
   *   The called MyrouteNoindex entity.
   */
  public function setWeight($weight);



  /**
   * Gets the items.
   *
   * @return array
   *   items of the MyrouteNoindex.
   */
  public function getItems();

  /**
   * Sets the MyrouteNoindex items.
   *
   * @param array $items
   *   The MyrouteNoindex items.
   *
   * @return \Drupal\myroute_noindex\MyrouteNoindexInterface
   *   The called MyrouteNoindex entity.
   */
  public function setItems($items);



  /**
   * Gets logic used to compute, either 'and' or 'or'.
   *
   * @return string
   *   Either 'and' or 'or'.
   */
  public function getLogic();

  /**
   * Sets logic used to compute, either 'and' or 'or'.
   *
   * @param string $logic
   *   Either 'and' or 'or'.
   */
  public function setLogic($logic);


  /**
   * Returns the conditions.
   *
   * @return \Drupal\Core\Condition\ConditionInterface[]|\Drupal\Core\Condition\ConditionPluginCollection
   *   An array of configured condition plugins.
   */
  public function getConditions();


  /**
   * Returns the condition.
   *
   * @param string $condition_id
   * @return \Drupal\Core\Condition\ConditionInterface
   *   condition.
   */
  public function getCondition($condition_id);


  /**
   *  Add condition to Conditions.
   *
   * @param array $configuration
   * @return \Drupal\Core\Condition\ConditionInterface
   *   condition.
   */
  public function addCondition($configuration);


  /**
   *  Remove condition from Conditions.
   *
   * @param string $condition_id
   * @return \Drupal\myroute_noindex\MyrouteNoindexInterface
   *   The called MyrouteNoindex entity.
   */
  public function removeCondition($condition_id);


  

  
}
