<?php
namespace Drupal\pubs_entity_type;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\user\EntityOwnerInterface;
use Drupal\Core\Entity\EntityChangedInterface;

/**
 * Provides an interface defining a PubsEntity entity.
 * @ingroup pubs_entity_type
 *
 */

interface PubsEntityInterface extends ContentEntityInterface, EntityOwnerInterface, EntityChangedInterface {
}
