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
    $form['revision_log_message']['#access'] = false;
    if ($entity->field_from_feed->value != 0) {
      $form['weight']['#access'] = false;
      $form['field_product_id']['#access'] = false;
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

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    parent::validateForm($form, $form_state);
    $entity = $form_state->getFormObject()->getEntity();
    $existing = \Drupal::entityTypeManager()->getStorage('pubs_entity')->loadByProperties(['field_product_id' => $form_state->getValue('field_product_id')[0]]);
    if ($entity->isNew()) {
      if (count($existing) > 0) {
        $form_state->setErrorByName('field_product_id', $this->t("A publication entity already exists with this ID"));
      }
    } else {
      if ($entity->field_product_id->value != $form_state->getValue('field_product_id')[0]['value']) {
        $form_state->setErrorByName('field_product_id', $this->t("The publication ID may not be changed on existing entities"));
      } else if (count($existing) == 1 && array_key_exists($entity->id(), $existing)) {
        //editing existing
      } else {
        debug("Unknown Product ID error");//TODO remove after testing
      }
    }
  }


}
