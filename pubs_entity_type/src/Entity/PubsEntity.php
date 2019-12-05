<?php
namespace Drupal\pubs_entity_type\Entity;

use Drupal\Core\Entity\EditorialContentEntityBase;
use Drupal\Core\Entity\EntityChangedTrait;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\user\UserInterface;
use Drupal\pubs_entity_type\PubsEntityInterface;
use Drupal\Core\Entity\EntityPublishedInterface;
use Drupal\Core\Entity\EntityPublishedTrait;
use Symfony\Component\HttpFoundation\RedirectResponse;

/**
 * Defines pubs_entity entity class
 *
 *  @ingroup pubs_entity_type
 *  @ContentEntityType(
 *    id = "pubs_entity",
 *    label = @Translation("Pubs Entity Type"),
 *    handlers = {
 *     "view_builder" = "Drupal\Core\Entity\EntityViewBuilder",
 *     "views_data" = "Drupal\views\EntityViewsData",
 *     "form" = {
 *       "add" = "Drupal\pubs_entity_type\Form\PubsEntityForm",
 *       "edit" = "Drupal\pubs_entity_type\Form\PubsEntityForm",
 *       "delete" = "Drupal\pubs_entity_type\Form\PubsEntityDeleteForm",
 *     },
 *     "access" = "Drupal\pubs_entity_type\PubsEntityAccessControlHandler",
 *   },
 *    base_table = "pubs_entity",
 *    revision_table = "pubs_entity_revision",
 *    revision_data_table = "pubs_entity_field_revision",
 *    admin_permission = "administer pubs entity",
 *    fieldable = TRUE,
 *    links = {
 *      "canonical" = "/pubs_entity/{pubs_entity}",
 *      "add-page" = "/pubs_entity/add",
 *      "edit-form" = "/pubs_entity/{pubs_entity}/edit",
 *      "delete-form" = "/pubs_entity/{pubs_entity}/delete",
 *      "collection" = "/pubs_entity/admin",
 *    },
 *    entity_keys = {
 *      "id" = "id",
 *      "uuid" = "uuid",
 *      "label" = "name",
 *      "published" = "status",
 *      "revision" = "revision_id",
 *      "status" = "status",
 *    },
 *    revision_metadata_keys = {
 *      "revision_user" = "revision_user",
 *      "revision_created" = "revision_created",
 *      "revision_log_message",
 *    },
 *    field_ui_base_route = "pubs_entity.pubs_entity_settings",
 *  )
 *
*/
class PubsEntity extends EditorialContentEntityBase implements PubsEntityInterface, EntityPublishedInterface {
  use EntityChangedTrait;

  /**
  * {@inheritdoc}
  * Set computed fields when creating a new Pubs Entity
  */
  public static function preCreate(EntityStorageInterface $storage, array &$values) {
    parent::preCreate($storage, $values);
    $values += array(
      'user_id' => \Drupal::currentUser()->id(),
    );
  }

  /**
  * {@inheritdoc}
  */
  public function getCreatedTime() {
    return $this->get('created')->value;
  }

  /**
  * {@inheritdoc}
  */
  public function getOwner() {
    return $this->get('user_id')->entity;
  }

  /**
  * {@inheritdoc}
  */
  public function getOwnerId() {
    return $this->get('user_id')->target_id;
  }

  /**
  * {@inheritdoc}
  */
  public function setOwnerId($uid) {
    $this->set('user_id', $uid);
    return $this;
  }

  /**
  * {@inheritdoc}
  */
  public function setOwner(UserInterface $account) {
    $this->set('user_id', $account->id());
    return $this;
  }

  /**
  * {@inheritdoc}
  */
  public function isPublished() {
    return $this->getEntityKey('status');
  }

  /**
  * {@inheritdoc}
  */
  public function setPublished($published = NULL) {
    $this
      ->set('status', TRUE);
    return $this;
  }

  /**
  * {@inheritdoc}
  */
  public function setUnpublished() {
    $this
      ->set('status', FALSE);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function preSave(EntityStorageInterface $storage) {
    $url = \Drupal::config('pubs_entity_type.settings')->get('pubs_store_url');
    $url_host = parse_url($url, PHP_URL_HOST);
    //Only allow approved hosts
    if ($url_host == 'store.extension.iastate.edu' || $url_host == 'localhost') {
      try {
        if (0 === substr_compare($url, "/", -1)) {
          $raw = file_get_contents($url . $this->field_product_id->value);
        } else if (0 === substr_compare($url, ".json", -5)) {
          $raw = file_get_contents($url);
        } else {
          $raw = file_get_contents($url . "/" . $this->field_product_id->value);
        }

        $decoded = json_decode($raw, TRUE);

        if (is_array($decoded) && is_array($decoded[0])) {
          $items = $decoded;
        } else {
          $items = [$decoded];
        }
        $found = false;

        foreach ($items as $item) {
          if (array_key_exists('productID', $item) && $item['productID'] == $this->field_product_id->value) {
            $this->name->value = $item['title'];
            $this->field_image_url->value = $item['image'];
            $date = explode('/', $item['pubDate']);
            $formatDate = $date[1] . '-' . (($date[0] < 10) ? '0' . $date[0] : $date[0]) . '-01';
            $this->field_publication_date->value = $formatDate;
            $found = true;
          }
        }
        if (!$found) {
          $response = new RedirectResponse(\Drupal::request()->getRequestUri());
          $response->send();
          drupal_set_message(t('Provided product ID was not found in the given feed'), 'error');
        }
      } catch (\Exception $e) {
        drupal_set_message(t('An Error occured pulling data from the given url'), 'error');
      }
    }

    parent::preSave($storage);
  }

  /**
   * {@inheritdoc}
   * Mark for reindex if used in search
   */
  public function postSave(EntityStorageInterface $storage, $update = TRUE) {
    parent::postSave($storage, $update);
  }

  /**
  * {@inheritdoc}
  *
  * Creates Fields and properties
  * Defines gui behavior
  */
  public static function baseFieldDefinitions(EntityTypeInterface $entity_type) {
    $fields = parent::baseFieldDefinitions($entity_type);

    $fields['id'] = BaseFieldDefinition::create('integer')
      ->setLabel(t('ID'))
      ->setReadOnly(TRUE);

    $fields['uuid'] = BaseFieldDefinition::create('uuid')
      ->setLabel(t('UUID'))
      ->setReadOnly(TRUE);

    $fields['name'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Name'))
      ->setTranslatable(FALSE)
      ->setSettings(array(
        'default_value' => '',
        'max_length' => 225,
      ))
      ->setDisplayOptions('view', array(
        'type' => 'string',
        'label' => 'hidden',
        'weight' => 1,
        'region' => 'content',
      ))
      ->setDisplayOptions('form', array(
        'type' => 'label_text',
        'label' => 'hidden',
        'weight' => 1,
        'region' => 'content',
      ))
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    $fields['field_product_id'] = BaseFieldDefinition::create('integer')
      ->setLabel(t('Product ID'))
      ->setTranslatable(FALSE)
      ->setRequired(TRUE)
      //->addConstraint('UniquePubId')
      ->setSettings(array(
        'default_value' => '',
        'max_length' => 225,
      ))
      ->setDisplayOptions('form', array(
        'type' => 'number',
        'weight' => 1,
        'region' => 'content',
        'settings' => array(
          'size' => 60,
          'placeholder' => '',
        ),
      ))
      ->setDisplayConfigurable('form', TRUE);

    $fields['field_image_url'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Image URL'))
      ->setTranslatable(FALSE)
      ->setRevisionable(TRUE)
      ->setSettings(array(
        'default_value' => '',
        'max_length' => 225,
      ))
      ->setDisplayOptions('view', array(
        'type' => 'remote_pubs_image',
        'weight' => 2,
        'region' => 'content',
        'label' => 'hidden',
      ))
      ->setDisplayOptions('form', array(
        'type' => 'remote_pubs_image',
        'weight' => 2,
        'region' => 'content',
        'label' => 'hidden',
      ))
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    $fields['field_publication_date'] = BaseFieldDefinition::create('datetime')
      ->setLabel(t('Product Publication Date'))
      ->setTranslatable(FALSE)
      ->setRevisionable(TRUE)
      ->setRequired(TRUE)
      ->setSettings(array(
        'datetime_type' => 'date'
      ))
      ->setDisplayOptions('view', array(
        'type' => 'datetime_custom',
        'settings' => [
          'date_format' => 'm/Y',
          ],
        'label' => 'hidden',
        'weight' => 3,
        'region' => 'content',
      ))
      ->setDisplayConfigurable('view', TRUE);

    $fields['field_from_feed'] = BaseFieldDefinition::create('boolean')
      ->setLabel(t('From Feed'))
      ->setTranslatable(FALSE)
      ->setRequired(FALSE)
      ->setSettings(array(
        'default_value' => FALSE,
      ));

      $fields['weight'] = BaseFieldDefinition::create('integer')
        ->setLabel(t('Weight'))
        ->setTranslatable(TRUE)
        ->setRevisionable(TRUE)
        ->setSettings(array(
          'max_length' => 255,
        ))
        ->setDefaultValue(0)
        ->setDisplayOptions('form', array(
          '#type' => 'weight',
          'weight' => 20,
          'region' => 'content',
          'label' => 'inline',
          'settings' => array(
            'size' => 60,
            'placeholder' => '',
          ),
        ))
      ->setDisplayConfigurable('form', TRUE);


    $fields['user_id'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(t('User Name'))
      ->setSettings(array(
        'target_type' => 'user',
        'handler' => 'default',
      ))
      ->setDisplayOptions('form', array(
        'type' => 'entity_reference_autocomplete',
        'settings' => array(
          'match_operator' => 'CONTAINS',
          'size' => 60,
          'autocomplete_type' => 'tags',
          'placeholder' => '',
        ),
      ))
      ->setDisplayConfigurable('form', TRUE);

    $fields['status'] = BaseFieldDefinition::create('boolean')
      ->setLabel(t('Published status'))
      ->setRevisionable(TRUE)
      ->setTranslatable(TRUE)
      ->setDefaultValue(TRUE)
      ->setDisplayOptions('form', array(
        'type' => 'string',
        'label' => 'hidden',
        'weight' => 21,
      ))
      ->setDisplayConfigurable('form', TRUE);

    $fields['created'] = BaseFieldDefinition::create('created')
      ->setLabel(t('Created'));

    $fields['changed'] = BaseFieldDefinition::create('changed')
      ->setLabel(t('Changed'))
      ->setTranslatable(TRUE)
      ->setRevisionable(TRUE);

    $fields['langcode'] = BaseFieldDefinition::create('language')
      ->setLabel(t('Language code'));

    return $fields;
  }
}
