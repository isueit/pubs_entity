<?php
namespace Drupal\pubs_entity_type\Plugin\Field\FieldWidget;
use \Drupal\Core\Field\FieldItemListInterface;
use \Drupal\Core\Field\FieldItemInterface;
use \Drupal\Core\Field\WidgetBase;

/**
 * @FieldWidget(
 *   id = "remote_pubs_image",
 *   label = @Translation("Pubs Entity Image"),
 *   field_types = {
 *     "link", "string", "string_long"
 *   }
 * )
 */
class PubImageWidget extends WidgetBase {
  /**
   * {@inheritdoc}
   */
  public function formElement(FieldItemListInterface $items, $delta, array $element, array &$form, $form_state) {
    $element = [];
    foreach ($items as $delta => $item) {
      if ($item->value != "") {
        $element[$delta] = [
          '#type' => 'markup',
          '#markup' => $this->getEmbedCode($item),
          '#allowed_tags' => ['img', 'a', 'div'],
        ];
      }
    }
    return $element;
  }

  /**
   * {@inheritdoc}
   */
  protected function getEmbedCode($item) {
    $url = "";
    if (is_string($item)) {
      $url = $item;
    } elseif ($item instanceof FieldItemInterface) {
      $class = get_class($item);
      $property = $class::mainPropertyName();
      if ($property) {
        $url = $item->value;
      }
    }
    return "<img src='" . $url . "' alt='Publication Image'>";
  }
}
