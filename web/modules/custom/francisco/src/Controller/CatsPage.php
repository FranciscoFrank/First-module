<?php
namespace Drupal\francisco\Controller;

use Drupal\Core\Controller\ControllerBase;

class CatsPage extends ControllerBase {
    public function CatsPage() {
        return [
            '#markup' => 'Hello! You can add here a photo of your cat.',
        ];
    }
}