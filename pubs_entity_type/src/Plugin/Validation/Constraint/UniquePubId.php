<?php
namespace Drupal\pubs_entity_type\Plugin\Validation\Constraint;
use Symfony\Component\Validator\Constraint;

/**
 * Checks to make sure product id is not duplicated
 *
 * @Constraint(
 *  id = "UniquePubId",
 *  label = @Translation("Unique Pub ID", context = "Validation"),
 *  type = "string"
 * )
 */
class UniquePubId extends Constraint {
  public $notUnique = 'Publication %value entity already exists on this site';
}
