<?php

namespace Drupal\myroute_noindex\Form;


use Drupal\Core\Entity\EntityForm;
use Drupal\Core\Form\FormStateInterface;
use Drupal\myroute_noindex\Entity\MyrouteNoindex;
use Drupal\Component\Serialization\Json;
use Drupal\Component\Utility\NestedArray;
use Drupal\Core\Url;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Messenger\MessengerInterface;

/**
 * Entity form for MyrouteNoindex entity.
 */
class MyrouteNoindexForm extends EntityForm implements ContainerInjectionInterface {


  /**
   * The messenger service.
   *
   * @var \Drupal\Core\Messenger\MessengerInterface
   */
  protected $messenger;


  /**
   * Constructs
   *
   * @param \Drupal\Core\Messenger\MessengerInterface $messenger
   *   The messenger service.
   */
  public function __construct(MessengerInterface $messenger) {
    $this->messenger = $messenger;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    /* @var MessengerInterface $messenger */
    $messenger = $container->get('messenger');
    return new static(
      $messenger
    );
  }


  /**
   * {@inheritdoc}
   */
  public function form(array $form, FormStateInterface $form_state) {
    $form = parent::form($form, $form_state);
    /** @var \Drupal\myroute_noindex\Entity\MyrouteNoindex $myroute_noindex */
    $myroute_noindex = $this->entity;
    $form['help'] = [
      '#markup' => '
        <ul>
          <li><strong>Укажите типы страниц для каких сработает шаблон:</strong><br />
          <em>роут</em> - один из зарегистрированных на сайте (нода, термин, товар, ...)<br /> 
          <em>условия</em> - для фильтрации страниц, любые доступные на сайте (словарь таксономии, тип ноды, текущая тема, ...). Если в списке нет - его нужно написать наследуя класс ConditionPluginBase</li>
          <li><strong>Заполните значение метатега robots:</strong><br />
          <em>noindex, follow</em> или <em>noindex, nofollow</em></li>
        </ul>',
      '#weight' => -10,
    ];
    $form['label'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('Label'),
      '#maxlength' => 255,
      '#default_value' => $myroute_noindex->label(),
      '#required' => TRUE,
    );
    $form['id'] = array(
      '#type' => 'machine_name',
      '#default_value' => $myroute_noindex->id(),
      '#machine_name' => array(
        'exists' => '\Drupal\myroute_noindex\Entity\MyrouteNoindex::load',
      ),
      '#disabled' => !$myroute_noindex->isNew(),
    );
    $form['route_name'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Route Name'),
      '#maxlength' => 255,
      '#default_value' => $myroute_noindex->getRouteName(),
      '#required' => TRUE,
      '#autocomplete_route_name' => 'myroute_noindex.router_autocomplete',
      '#description' => 'Если правило не зависит от роута, испльзуйте <strong>none</strong> (не рекомендуется, но иногда полезно)',
    ];
    if (!$myroute_noindex->isNew()) {
      $form['items_section'] = $this->createItemsSet($form, $form_state, $myroute_noindex);
      $form['conditions_section'] = $this->createConditionsSet($form, $myroute_noindex);
      $form['logic'] = [
        '#type' => 'radios',
        '#options' => [
          'and' => $this->t('All conditions must pass'),
          'or' => $this->t('Only one condition must pass'),
        ],
        '#default_value' => $myroute_noindex->getLogic(),
      ];
    }
    return $form;
  }


  protected function createItemsSet(array $form, FormStateInterface $form_state, MyrouteNoindex $myroute_noindex) {
    $items = $myroute_noindex->getItems();
    $form['items_section'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('Settings'),
      '#open' => TRUE,
      '#prefix' => '<div id="items-section-fieldset-wrapper">',
      '#suffix' => '</div>',
    ];
    $form['items_section']['items'] = [
      '#tree' => TRUE,
    ];
    $form['items_section']['items']['noindex_type'] = [
      '#title' => 'Значение метатега robots',
      '#type' => 'select',
      '#options' => [
        'noindex, follow' => 'Noindex, follow',
        'noindex, nofollow' => 'Noindex, nofollow',
      ],
      '#default_value' => !empty($items['noindex_type']) ? $items['noindex_type'] : 'noindex, follow',
      '#required' => TRUE,
    ];
    return $form['items_section'];
  }


  protected function createConditionsSet(array $form, MyrouteNoindex $myroute_noindex) {
    $attributes = [
      'class' => ['use-ajax'],
      'data-dialog-type' => 'modal',
      'data-dialog-options' => Json::encode([
        'width' => 'auto',
      ]),
    ];
    $add_button_attributes = NestedArray::mergeDeep($attributes, [
      'class' => [
        'button',
        'button--small',
        'button-action',
        'form-item',
      ],
    ]);
    $form['conditions_section'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('Conditions'),
      '#open' => TRUE,
    ];
    $form['conditions_section']['add_condition'] = [
      '#type' => 'link',
      '#title' => $this->t('Add new condition'),
      '#url' => Url::fromRoute('myroute_noindex.condition_select', [
        'myroute_noindex' => $myroute_noindex->id(),
      ]),
      '#attributes' => $add_button_attributes,
      '#attached' => [
        'library' => [
          'core/drupal.ajax',
        ],
      ],
    ];
    if ($conditions = $myroute_noindex->getConditions()) {
      $form['conditions_section']['conditions'] = [
        '#type' => 'table',
        '#header' => [
          $this->t('Label'),
          $this->t('Description'),
          $this->t('Operations'),
        ],
        '#empty' => $this->t('There are no conditions.'),
      ];
      foreach ($conditions as $condition_id => $condition) {
        /* @var \Drupal\Core\Condition\ConditionPluginBase $condition */
        $row = [];
        $row['label']['#markup'] = $condition->getPluginDefinition()['label'];
        $row['description']['#markup'] = $condition->summary();
        $operations = [];
        $operations['edit'] = [
          'title' => $this->t('Edit'),
          'url' => Url::fromRoute('myroute_noindex.condition_edit', [
            'myroute_noindex' => $myroute_noindex->id(),
            'condition_id' => $condition_id,
          ]),
          'attributes' => $attributes,
        ];
        $operations['delete'] = [
          'title' => $this->t('Delete'),
          'url' => Url::fromRoute('myroute_noindex.condition_delete', [
            'myroute_noindex' => $myroute_noindex->id(),
            'condition_id' => $condition_id,
          ]),
          'attributes' => $attributes,
        ];
        $row['operations'] = [
          '#type' => 'operations',
          '#links' => $operations,
        ];
        $form['conditions_section']['conditions'][$condition_id] = $row;
      }
    }
    return $form['conditions_section'];
  }


  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    $myroute_noindex = $this->entity;
    $is_new = $myroute_noindex->isNew();
    $status = $myroute_noindex->save();
    if ($status) {
      $this->messenger->addStatus($this->t('Saved the %label MyrouteNoindex.', array(
        '%label' => $myroute_noindex->label(),
      )));
    }
    else {
      $this->messenger->addStatus($this->t('The %label MyrouteNoindex was not saved.', array(
        '%label' => $myroute_noindex->label(),
      )));
    }
    if ($is_new) {
      $form_state->setRedirectUrl($myroute_noindex->toUrl('edit-form'));
    }
    else {
      $form_state->setRedirectUrl($myroute_noindex->toUrl('collection'));
    }
  }


}
