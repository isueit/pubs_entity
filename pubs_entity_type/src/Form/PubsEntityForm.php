<?php

namespace Drupal\pubs_entity_type\Form;

use Drupal\Core\Entity\ContentEntityForm;
use Drupal\Core\Form\FormStateInterface;
use Drupal\pubs_entity_type;

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

    //Update entity, new entities handled in entity defintion class
    if (array_key_exists('validated_information', $form) && !$entity->isNew()) {
      $validate = $form['validated_information'];
      $entity->name->value = $validate->title;
      $entity->field_image_url->value = $validate->image;
      $date = explode('/', $validate->pubDate);
      $formatDate = $date[1] . '-' . (($date[0] < 10) ? '0' . $date[0] : $date[0]) . '-01';
      $entity->field_publication_date->value = $formatDate;
      $entity->save();
    }

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
    $validate = validatePubsEntity($form_state->getValue('field_product_id')[0]['value'], $entity);
    switch ($validate) {
      case 'NaN':
        $form_state->setErrorByName('field_product_id', $this->t("Product ID must be a positive whole number"));//Do not include id, no need to if it is not an id
        return false;
        break;
      case 'Entity with ID already exists':
        $form_state->setErrorByName('field_product_id', $this->t("A publication entity already exists with ID: " . $form_state->getValue('field_product_id')[0]['value']));
        return false;
        break;
      case 'Null entity':
        $form_state->setErrorByName('field_product_id', $this->t("Error creating entity"));
        return false;
        break;
      case 'Product with ID not found':
        $form_state->setErrorByName('field_product_id', $this->t("Product with given ID: " . $form_state->getValue('field_product_id')[0]['value'] . " not found"));
        return false;
        break;
      case 'Exception thrown':
        $form_state->setErrorByName('field_product_id', $this->t("Exception thrown while trying to open Product Feed"));
        return false;
        break;
      case 'Invalid url host':
        $form_state->setErrorByName('field_product_id', $this->t("Invalid feed host"));
        return false;
        break;
      case 'Unknown Error':
        $form_state->setErrorByName('field_product_id', $this->t("Unknown Error"));
        return false;
        break;
      default:
        $form['validated_information'] = $validate;
        return true;
        break;
    }
  }
}
