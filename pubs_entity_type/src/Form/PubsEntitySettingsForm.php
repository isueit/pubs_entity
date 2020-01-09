<?php
namespace Drupal\pubs_entity_type\Form;


use \Drupal\Core\Form\ConfigFormBase;
use \Drupal\Core\Form\FormStateInterface;
use \Drupal\Core\Config\ConfigFactoryInterface;

/**
 * Class PubsEntitySettingsForm.
 */
class PubsEntitySettingsForm extends ConfigFormBase {
  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      'pubs_entity_type.settings',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'pubs_entity_settings';
  }
  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    // Check for valid URL
    $url = $form_state->getValue('url');
    if (filter_var($url, FILTER_VALIDATE_URL) === FALSE) {
      $form_state->setErrorByName('url', $this->t('Not a valid URL'));
    }

    // Check for valid host, not sure if we need this test...
    $host = parse_url($url, PHP_URL_HOST);
    if (!($host == 'local.test' || $host == 'localhost' || $host == 'store.extension.iastate.edu')) {
      $form_state->setErrorByName('url', $host . $this->t(' is an invalid hostname'));
    }
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    parent::submitForm($form, $form_state);
    $this->config('pubs_entity_type.settings')
      ->set('pubs_store_url', $form_state->getValue('url'))
      ->save();
    ;
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('pubs_entity_type.settings');
    $form['url'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('Json Feed Url'),
      '#description' => $this->t('URL of page that will return information about an individual publication. The Publication ID number will be appended to this URL.'),
      '#default_value' => $config->get('pubs_store_url'),
      '#maxlength' => 256,
      '#size' => 64,
    );

    return parent::buildForm($form, $form_state);
  }
}
