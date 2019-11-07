<?php
namespace Drupal\pubs_entity_type\Plugin\Validation\Constraint;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

/**
 * Validates id does not already exist and is an integer
 */
class UniquePubIdValidator extends ConstraintValidator {
  /**
   * {@inheritdoc}
   */
  public function validate($items, Constraint $constraint) {
    foreach ($items as $item) {
      if (!$this->isUnique($item->value)) {
        $this->context->addViolation($constraint->notUnique, ['%value' => $item->value]);
      }
    }
  }

  /**
   * Check if there is a pub entity using this id
   */
   private function isUnique($value) {
     $matches = \Drupal::entityTypeManager()->getStorage('pubs_entity')->loadByProperties(['field_product_id' => $value]);
     return count($matches) == 0;
   }
}
