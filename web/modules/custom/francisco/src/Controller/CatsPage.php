<?php

namespace Drupal\francisco\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Form\FormBuilderInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class CatsPage.
 * 
 * Provides a page with a cat message.
 */
class CatsPage extends ControllerBase {

    /**
     * The form builder service.
     *
     * @var \Drupal\Core\Form\FormBuilderInterface
     */
    protected $formBuilder;

    /**
     * Constructs a CatsPage object.
     *
     * @param \Drupal\Core\Form\FormBuilderInterface $formBuilder
     *   The form builder service.
     */
    public function __construct(FormBuilderInterface $formBuilder) {
        $this->formBuilder = $formBuilder;
    }

    /**
     * {@inheritdoc}
     */
    public static function create(ContainerInterface $container) {
        return new static(
            $container->get('form_builder')
        );
    }

    /**
     * Returns a renderable array for the cats page.
     *
     * @return array
     *   Render array containing the page content.
     */
    public function content() {
        $form = $this->formBuilder->getForm('Drupal\francisco\Form\CatsForm');

        return [
            '#theme' => 'cats',
            '#content' => [
                '#description' => $this->t('Hello! You can add here a photo of your cat.'),
                '#form' => $form,
            ],
        ];
    }

}

