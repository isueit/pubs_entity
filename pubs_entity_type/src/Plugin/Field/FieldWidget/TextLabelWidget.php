<?php
namespace Drupal\pubs_entity_type\Plugin\Field\FieldWidget;
use \Drupal\Core\Field\FieldItemListInterface;
use \Drupal\Core\Field\FieldItemInterface;
use \Drupal\Core\Field\WidgetBase;

/**
 * @FieldWidget(
 *   id = "label_text",
 *   label = @Translation("Text Label"),
 *   field_types = {
 *     "string", "string_long"
 *   }
 * )
 */
class TextLabelWidget extends WidgetBase {
  /**
   * {@inheritdoc}
   */
  public function formElement(FieldItemListInterface $items, $delta, array $element, array &$form, $form_state) {
    $element = [];
    foreach ($items as $delta => $item) {
      if ($item->value != "") {
        $element[$delta] = [
          '#type' => 'html_tag',
          '#tag' => 'h4',
          '#value' => t($item->value)
        ];
      }
    }
    return $element;
  }
}
