<?php

namespace Drupal\francisco\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Form\FormBuilderInterface;

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
        $form = \Drupal::formBuilder()->getForm('Drupal\francisco\Form\CatsForm');

        return [
            '#theme' => 'cats',
            '#content' => $this->t('Hello! You can add here a photo of your cat.'),
            '#form' => $form,
        ];
    }

}

