<?php

namespace Drupal\pubs_entity_type\Form;

use Drupal\Core\Entity\ContentEntityConfirmFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;

/**
 * Form for deleting pubs entity
 * @ingroup pubs_entity_type
 */
class PubsEntityDeleteForm extends ContentEntityConfirmFormBase {
  /**
   * @return string
   *  The form question
   */
  public function getQuestion() {
    return $this->t("Are you sure that you want to delete the Publication entity for %pub?", array( '%pub' => $this->entity->label()));
  }

  /**
   * @return \Drupal\Core\Url
   *  A url object
   */
  public function getCancelUrl() {
    return new Url('<front>');
  }

  public function submitForm(array &$form, FormStateInterface $form_state) {
    $this->entity->delete();
    $form_state->setRedirectUrl($this->getCancelUrl());
  }
}
