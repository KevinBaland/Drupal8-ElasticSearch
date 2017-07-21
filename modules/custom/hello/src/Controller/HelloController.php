<?php
/**
 * @file
 * Contains \Drupal\hello\Controller\HelloController.
 */
namespace Drupal\hello\Controller;

use Drupal\Core\Controller\ControllerBase;


class HelloController extends ControllerBase {
    
  public function content($page) {
    return array(
        '#markup' => '' . t('Hello there! ') . '',
    );
  }
}