<?php 

namespace Drupal\francisco\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Messenger\MessengerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class CatsForm.
 * 
 * Creates a form for adding a cat.
 */
class CatsForm extends FormBase {
    
    /**
     * The messenger service.
     *
     * @var \Drupal\Core\Messenger\MessengerInterface
     */
    protected $messenger;

    /**
     * Constructs a CatsForm object.
     *
     * @param \Drupal\Core\Messenger\MessengerInterface $messenger
     *   The messenger service.
     */
    public function __construct(MessengerInterface $messenger) {
        $this->messenger = $messenger;
    }

    /**
     * {@inheritdoc}
     */
    public static function create(ContainerInterface $container) {
        return new static(
            $container->get('messenger')
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getFormId() {
        return 'cats_form';
    } 

    /**
     * {@inheritdoc}
     */
    public function buildForm(array $form, FormStateInterface $form_state) {
        $form['cat_name'] = [
            '#type' => 'textfield',
            '#title' => $this->t('Your cat\'s name:'),
            '#description' => $this->t('Minimum length is 2 characters and maximum length is 32.'),
            '#required' => TRUE,
            '#maxlength' => 32,
            '#attributes' => [
                'placeholder' => $this->t('Enter your cat\'s name'),
            ],
        ];

        $form['submit'] = [
            '#type' => 'submit',
            '#value' => $this->t('Add cat'),
        ];

        return $form;
    }

    /**
     * {@inheritdoc}
     */
    public function validateForm(array & $form, FormStateInterface $form_state) {
        $cat_name = $form_state->getValue('cat_name');
        
        if (strlen($cat_name) < 2 ) {
            $form_state->setErrorByName('cat_name', $this->t('The cat\'s name must be at least 2 characters long.'));
        }

        if (strlen($cat_name) > 32 ) {
            $form_state->setErrorByName('cat_name', $this->t('The cat\'s name must not exceed 32 characters.'));
        }
    }

    /**
     * {@inheritdoc}
     */
    public function submitForm(array & $form, FormStateInterface $form_state) {
        $this->messenger->addMessage($this->t('Your cat has been successfully added.'));
    }

}

