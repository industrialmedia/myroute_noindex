<?php

namespace Drupal\myroute_noindex\Controller;


use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Config\Entity\DraggableListBuilder;

/**
 * Provides a listing of MyrouteNoindex entities.
 */
class MyrouteNoindexListBuilder extends DraggableListBuilder {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'myroute_noindex_list_builder';
  }


  /**
   * {@inheritdoc}
   */
  public function buildHeader() {
    $header['label'] = 'Шаблоны';
    $header['weight'] = t('Weight');
    $header += parent::buildHeader();
    return $header;
  }

  /**
   * {@inheritdoc}
   */
  public function buildRow(EntityInterface $entity) {
    $row['label'] = $entity->label();
    $row += parent::buildRow($entity);
    return $row;
  }

  /**
   * {@inheritdoc}
   */
  public function render() {
    $build = parent::render();
    $build['help'] = [
      '#markup' => '<p>Первый шаблон из списка для которого все условия будут выполнены - будет использован.</p>',
      '#weight' => -10,
    ];
    return $build;
  }

}
