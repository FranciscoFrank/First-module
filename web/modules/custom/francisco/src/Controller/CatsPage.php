<?php

namespace Drupal\francisco\Controller;

use Drupal\Core\Controller\ControllerBase;

/**
 * Class CatsPage.
 * 
 * Provides a page with a cat message.
 */
class CatsPage extends ControllerBase {

    /**
     * Returns a renderable array for the cats page.
     *
     * @return array
     *   Render array containing the page content.
     */
    public function content() {
        return [
            '#markup' => $this->t('Hello! You can add here a photo of your cat.'),
        ];
    }

}

