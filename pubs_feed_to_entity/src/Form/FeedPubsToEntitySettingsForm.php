<?php
namespace Drupal\pubs_feed_to_entity\Form;

use \Drupal\Core\Form\ConfigFormBase;
use \Drupal\Core\Form\FormStateInterface;
use \Drupal\Core\Config\ConfigFactoryInterface;
use \Drupal\taxonomy\Entity\Term;

/**
 * Class FeedPubsToEntitySettingsForm.
 */
class FeedPubsToEntitySettingsForm extends ConfigFormBase {
  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      'pubs_feed_to_entity.settings',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'pubs_feed_to_entity_settings';
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    parent::submitForm($form, $form_state);
    FeedPubsToEntitySettingsForm::createPubsFromUrl($form_state->getValue('url'));
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {

    $form['url'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('Json Feed Url'),
      '#description' => $this->t('Feed url to pull objects from.'),
      '#maxlength' => 64,
      '#size' => 64,
    );

    return parent::buildForm($form, $form_state);
  }


  /**
   * Pull in pubs entities from feed based on feed url
   */
  public function createPubsFromUrl($url) {
    $url_host = parse_url($url, PHP_URL_HOST);
    //Only allow approved hosts
    if ($url_host == 'store.extension.iastate.edu' || $url_host == 'localhost') {
      try {
        $raw = file_get_contents($url);
        $items = json_decode($raw, TRUE);
        $entity_config = \Drupal::service('config.factory')->getEditable('pubs_entity_type.settings');
        $saved_url = $entity_config->get('pubs_store_url');
        $entity_config->set('pubs_store_url', $url);
        $entity_config->save();

        foreach ($items as $item) {
          //Prevent duplicates
          $existing = \Drupal::entityTypeManager()->getStorage('pubs_entity')->loadByProperties(['field_product_id' => $item['productID']]);
          if (count($existing) == 0) {
            $newEntity = \Drupal\pubs_entity_type\Entity\PubsEntity::create([
              'field_product_id' => $item['productID'],
              'field_from_feed' => 1,
            ]);//Post save finds the other fields
            $newEntity->setPublished();
            $newEntity->save();
          }
        }
        $entity_config->set('pubs_store_url', $saved_url);
        $entity_config->save();
      } catch (\Exception $e) {
        $entity_config->set('pubs_store_url', $saved_url);
        $entity_config->save();
        drupal_set_message(t('An Error occured pulling data from the given url'), 'error');
      }
    } else {
      drupal_set_message(t('Only feeds from Extension Store allowed'), 'error');
    }
  }
}
