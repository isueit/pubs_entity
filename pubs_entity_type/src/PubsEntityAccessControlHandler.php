<?php
namespace Drupal\pubs_entity_type;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Entity\EntityAccessControlHandler;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Session\AccountInterface;

/**
 * Provides access control for pubs_entity
 */
class PubsEntityAccessControlHandler extends EntityAccessControlHandler {
  /**
   * {@inheritdoc}
   */
  protected function checkAccess(EntityInterface $entity, $operation, AccountInterface $account) {
    switch ($operation) {
      case 'view':
        return AccessResult::allowedIfHasPermission($account, 'view pubs entity');
        break;
      case 'edit':
        return AccessResult::allowedIfHasPermission($account, 'edit pubs entity');
        break;
      case 'delete':
        return AccessResult::allowedIfHasPermission($account, 'delete pubs entity');
        break;
    }
    return AccessResult::forbidden();
  }

  /**
   * {@inheritdoc}
   */
   protected function checkCreateAccess(AccountInterface $account, array $context, $entity_bundle = NULL) {
     return AccessResult::allowedIfHasPermission($account, 'add pubs entity');
   }

  /**
  * {@inheritdoc}
  */
  protected function checkFieldAccess($operation, $field_definition, $account, $items = NULL) {
    return AccessResult::allowedIfHasPermission($account, 'access content');
  }
}
