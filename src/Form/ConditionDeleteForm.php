<?php

namespace Drupal\myroute_noindex\Form;


use Drupal\myroute_noindex\Entity\MyrouteNoindex;
use Drupal\Core\Form\ConfirmFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Messenger\MessengerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a form for deleting an condition.
 */
class ConditionDeleteForm extends ConfirmFormBase implements ContainerInjectionInterface {



  /**
   * The messenger service.
   *
   * @var \Drupal\Core\Messenger\MessengerInterface
   */
  protected $messenger;

  /**
   * The myroute_noindex entity this selection condition belongs to.
   *
   * @var \Drupal\myroute_noindex\Entity\MyrouteNoindex
   */
  protected $myroute_noindex;

  /**
   * The condition used by this form.
   *
   * @var \Drupal\Core\Condition\ConditionInterface
   */
  protected $condition;


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
    /* @var \Drupal\Core\Messenger\MessengerInterface $messenger */
    $messenger = $container->get('messenger');
    return new static(
      $messenger
    );
  }


  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'myroute_noindex_condition_delete_form';
  }

  /**
   * {@inheritdoc}
   */
  public function getQuestion() {
    return $this->t('Are you sure you want to delete the condition %name?', ['%name' => $this->condition->getPluginDefinition()['label']]);
  }

  /**
   * {@inheritdoc}
   */
  public function getCancelUrl() {
    return $this->myroute_noindex->urlInfo('edit-form');
  }

  /**
   * {@inheritdoc}
   */
  public function getConfirmText() {
    return $this->t('Delete');
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, MyrouteNoindex $myroute_noindex = NULL, $condition_id = NULL) {
    $this->myroute_noindex = $myroute_noindex;
    $this->condition = $myroute_noindex->getCondition($condition_id);
    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $this->myroute_noindex->removeCondition($this->condition->getConfiguration()['uuid']);
    $this->myroute_noindex->save();
    $this->messenger->addStatus($this->t('The condition %name has been removed.', ['%name' => $this->condition->getPluginDefinition()['label']]));
    $form_state->setRedirectUrl(Url::fromRoute('entity.myroute_noindex.edit_form', [
      'myroute_noindex' => $this->myroute_noindex->id(),
    ]));
  }

}
