<?php

namespace Drupal\myroute_noindex;

use Drupal\myroute_noindex\Entity\MyrouteNoindex;
use Drupal\Component\Plugin\Exception\ContextException;
use Drupal\Core\Condition\ConditionAccessResolverTrait;
use Drupal\Core\Condition\ConditionPluginCollection;
use Drupal\Core\Plugin\Context\ContextHandlerInterface;
use Drupal\Core\Plugin\Context\ContextRepositoryInterface;
use Drupal\Core\Plugin\ContextAwarePluginInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;

/**
 * Class ConditionEvaluator.
 *
 * @package Drupal\myroute_noindex
 */
class MyrouteNoindexEvaluator {

  use ConditionAccessResolverTrait;

  /**
   * The plugin context handler.
   *
   * @var \Drupal\Core\Plugin\Context\ContextHandlerInterface
   */
  protected $contextHandler;

  /**
   * The context manager service.
   *
   * @var \Drupal\Core\Plugin\Context\ContextRepositoryInterface
   */
  protected $contextRepository;

  /**
   * The entity type manager service.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;


  /**
   * @var array
   */
  protected $evaluations = [];


  /**
   * @var array
   */
  protected $evaluations_by_route_name = [];


  /**
   * Constructor.
   *
   * @param \Drupal\Core\Plugin\Context\ContextHandlerInterface $context_handler
   *   The plugin context handler.
   * @param \Drupal\Core\Plugin\Context\ContextRepositoryInterface $context_repository
   *   The lazy context repository service.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager service.
   */
  public function __construct(ContextHandlerInterface $context_handler, ContextRepositoryInterface $context_repository, EntityTypeManagerInterface $entity_type_manager) {
    $this->contextRepository = $context_repository;
    $this->contextHandler = $context_handler;
    $this->entityTypeManager = $entity_type_manager;
  }

  public function evaluateByRouteName($route_name) {
    if (!isset($this->evaluations_by_route_name[$route_name])) {

      $query = \Drupal::entityQuery('myroute_noindex')
        ->condition('route_name', [$route_name, 'none'], 'IN');
      $ids = $query->execute();
      $myroute_noindexs = $this->entityTypeManager->getStorage('myroute_noindex')->loadMultiple($ids);
      
      uasort($myroute_noindexs, function (MyrouteNoindex $a, MyrouteNoindex $b) {
        $a = $a->getWeight();
        $b = $b->getWeight();
        if ($a == $b) {
          return 0;
        }
        return ($a < $b) ? -1 : 1;
      });
      if (!empty($myroute_noindexs)) {
        foreach ($myroute_noindexs as $myroute_noindex) {
          /** @var \Drupal\myroute_noindex\Entity\MyrouteNoindex $myroute_noindex */
          if ($this->evaluate($myroute_noindex)) {
            $this->evaluations_by_route_name[$route_name] = $myroute_noindex->id();
            return $this->evaluations_by_route_name[$route_name]; // Сразу возращаем, берем 1й из списка
          }
        }
      }
      if (!isset($this->evaluations_by_route_name[$route_name])) {
        $this->evaluations_by_route_name[$route_name] = FALSE;
      }
    }
    return $this->evaluations_by_route_name[$route_name];
  }

  /**
   * @param \Drupal\myroute_noindex\Entity\MyrouteNoindex $myroute_noindex
   *
   * @return boolean
   */
  public function evaluate(MyrouteNoindex $myroute_noindex) {
    $id = $myroute_noindex->id();
    if (!isset($this->evaluations[$id])) {
      /** @var ConditionPluginCollection $conditions */
      $conditions = $myroute_noindex->getConditions();
      if (empty($conditions)) {
        return TRUE;
      }
      $logic = $myroute_noindex->getLogic();
      if ($this->applyContexts($conditions, $logic)) {
        /** @var \Drupal\Core\Condition\ConditionInterface[] $conditions */
        $this->evaluations[$id] = $this->resolveConditions($conditions, $logic);
      }
      else {
        $this->evaluations[$id] = FALSE;
      }
    }
    return $this->evaluations[$id];
  }

  /**
   * @param \Drupal\Core\Condition\ConditionPluginCollection $conditions
   * @param string $logic
   *
   * @return bool
   */
  protected function applyContexts(ConditionPluginCollection &$conditions, $logic) {
    $have_1_testable_condition = FALSE;
    foreach ($conditions as $id => $condition) {
      if ($condition instanceof ContextAwarePluginInterface) {
        try {
          $contexts = $this->contextRepository->getRuntimeContexts(array_values($condition->getContextMapping()));
          //dump($contexts);
          $this->contextHandler->applyContextMapping($condition, $contexts);
          $have_1_testable_condition = TRUE;
        } catch (ContextException $e) {
          if ($logic == 'and') {
            // Logic is all and found condition with contextException.
            return FALSE;
          }
          $conditions->removeInstanceId($id);
        }

      }
      else {
        $have_1_testable_condition = TRUE;
      }
    }
    if ($logic == 'or' && !$have_1_testable_condition) {
      return FALSE;
    }
    return TRUE;
  }

}
