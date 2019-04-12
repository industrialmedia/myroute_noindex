<?php

namespace Drupal\myroute_noindex\Controller;

use Drupal\myroute_noindex\Entity\MyrouteNoindex;
use Drupal\Component\Serialization\Json;
use Drupal\Core\Condition\ConditionManager;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Url;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class MyrouteNoindexConditionController.
 *
 * @package Drupal\myroute_noindex\Controller
 */
class MyrouteNoindexConditionController extends ControllerBase {

  /**
   * Drupal\Core\Condition\ConditionManager definition.
   *
   * @var \Drupal\Core\Condition\ConditionManager
   */
  protected $conditionManager;

  /**
   * Constructs
   *
   * @param \Drupal\Core\Condition\ConditionManager $condition_manager
   *   The condition manager.
   */
  public function __construct(ConditionManager $condition_manager) {
    $this->conditionManager = $condition_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    /* @var \Drupal\Core\Condition\ConditionManager $condition_manager */
    $condition_manager = $container->get('plugin.manager.condition');
    return new static(
      $condition_manager
    );
  }


  /**
   * Presents a list of conditions to add to the myroute_noindex entity.
   *
   * @param \Drupal\myroute_noindex\Entity\MyrouteNoindex $myroute_noindex
   *   The myroute_noindex entity
   * @return array
   *   The condition selection page.
   */
  public function selectCondition(MyrouteNoindex $myroute_noindex) {
    $build = [
      '#theme' => 'links',
      '#links' => [],
    ];
    $available_plugins = $this->conditionManager->getDefinitions();
    foreach ($available_plugins as $condition_id => $condition) {
      $build['#links'][$condition_id] = [
        'title' => $condition['label'],
        'url' => Url::fromRoute('myroute_noindex.condition_add', [
          'myroute_noindex' => $myroute_noindex->id(),
          'condition_id' => $condition_id,
        ]),
        'attributes' => [
          'class' => ['use-ajax'],
          'data-dialog-type' => 'modal',
          'data-dialog-options' => Json::encode([
            'width' => 'auto',
          ]),
        ],
      ];
    }
    return $build;
  }

}
