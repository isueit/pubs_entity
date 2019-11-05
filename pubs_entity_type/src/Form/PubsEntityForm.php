<?php

namespace Drupal\pubs_entity_type\Form;

use Drupal\Core\Entity\ContentEntityForm;
use Drupal\Core\Form\FormStateInterface;

/**
 * Form controller for the pubs entity edit form
 * @ingroup pubs_entity_type
 */
class PubsEntityForm extends ContentEntityForm {
  public function getFormId() {
    return 'pubs_entity_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form = parent::buildForm($form, $form_state);
    $entity = $this->entity;
    //Second half checks for null value on required field, can be removed if they should be shown when first creating
    if ($entity->field_from_feed->value != 0 || $form_state->field_product_id->value == null) {
      $form['weight'] = null;
      $form['field_product_id'] = null;
    }
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    parent::submitForm($form, $form_state);

    $status = parent::save($form, $form_state);
    $entity = $this->entity;
    $entity->setNewRevision();

    if ($status == SAVED_UPDATED) {
      drupal_set_message($this->t('The publication %staff has been updated.', ['%staff' => $entity->toLink()->toString()]));
    } else {
      drupal_set_message($this->t('The publication %staff has been created.', ['%staff' => $entity->toLink()->toString()]));
    }

    $form_state->setRedirectUrl($this->entity->toUrl('collection'));
    return $status;
  }


}
