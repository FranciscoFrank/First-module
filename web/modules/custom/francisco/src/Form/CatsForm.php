<?php 

namespace Drupal\francisco\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Messenger\MessengerInterface;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\HtmlCommand;
use Drupal\Core\Ajax\InvokeCommand;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
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
        $form['#prefix'] = '<div class="cats-page__form-wrapper">';
        $form['#suffix'] = '</div>';

        $form['cat_name'] = [
            '#type' => 'textfield',
            '#title' => $this->t('Your cat\'s name:'),
            '#description' => $this->t('Minimum length is 2 characters and maximum length is 32.'),
            '#required' => TRUE,
            '#maxlength' => 32,
            '#attributes' => [
                'placeholder' => $this->t('Enter your cat\'s name'),
                'class' => ['cats-form__name-field'],
            ],
            '#ajax' => [
                'callback' => '::validateNameAjax',
                'event' => 'change',
            ],
        ];

        $form['validation_message'] = [
            '#type' => 'markup',
            '#prefix' => '<div class="cats-page__validation-message">',
            '#suffix' => '</div>',
        ];

        $form['submit'] = [
            '#type' => 'submit',
            '#value' => $this->t('Add cat'),
            '#ajax' => [
                'callback' => '::submitFormAjax',
                'event' => 'click',
            ],
        ];

        return $form;
    }

    /**
     * AJAX form for cat name validation.
     *
     * @param array $form
     * @param FormStateInterface $form_state
     * @return void
     */
    public function validateNameAjax(array &$form, FormStateInterface $form_state) {
        $response = new AjaxResponse();
        $cat_name = $form_state->getValue('cat_name');
        $error_messages = [];

        if (mb_strlen($cat_name) < 2) {
            $error_messages[] = $this->t('The cat\'s name must be at least 2 characters long.');
        }

        if (mb_strlen($cat_name) > 32) {
            $error_messages[] = $this->t('The cat\'s name must not exceed 32 characters.');
        }

        if (!empty($error_messages)) {
            $response->addCommand(new HtmlCommand(
                '.cats-page__validation-message', 
                '<div class="cats-page__validation-message messages messages--warning">'. 
                implode('<br>', $error_messages) . 
                '</div>'
            ));
            $response->addCommand(new InvokeCommand('.cats-form__name-field', 'removeClass', ['error']));
            $response->addCommand(new InvokeCommand('.cats-form__name-field', 'addClass', ['warning']));
        } else {
            $response->addCommand(new HtmlCommand('.cats-page__validation-message', ''));
            $response->addCommand(new InvokeCommand('.cats-form__name-field', 'removeClass', ['warning']));
            $response->addCommand(new InvokeCommand('.cats-form__name-field', 'removeClass', ['error'])); 
        }

        return $response;
    }


    /**
     * AJAX function for form submission.
     *
     * @param array $form
     * @param FormStateInterface $form_state
     * @return void
     */
    public function submitFormAjax(array &$form, FormStateInterface $form_state) {
        $response = new AjaxResponse();

        if ($form_state->hasAnyErrors()) {
            $error_messages = [];
            foreach ($form_state->getErrors() as $error) {
                $error_messages[] = $error;
            }
    
            $response->addCommand(new HtmlCommand(
                '.cats-page__validation-message', 
                '<div class="cats-page__validation-message messages messages--error">'. 
                implode('<br>', $error_messages). 
                '</div>'
            ));
            $response->addCommand(new InvokeCommand('.cats-form__name-field', 'removeClass', ['warning']));
            $response->addCommand(new InvokeCommand('.cats-form__name-field', 'addClass', ['error']));
        } else {
            $this->submitForm($form, $form_state);
    
            $response->addCommand(new InvokeCommand('.cats-form__name-field', 'removeClass', ['error']));
            $response->addCommand(new InvokeCommand('.cats-form__name-field', 'val', ['']));
            $response->addCommand(new HtmlCommand(
                '.cats-page__validation-message', 
                '<div class="cats-page__validation-message messages messages--status">'. 
                    $this->t('Your cat has been successfully added.'). 
                '</div>'
            ));
        }
    
        return $response;
    }    

    /** 
     * {@inheritdoc}
     */
    public function validateForm(array &$form, FormStateInterface $form_state) {
        $cat_name = $form_state->getValue('cat_name');
        
        if (mb_strlen($cat_name) < 2) {
            $form_state->setErrorByName('cat_name', $this->t('The cat\'s name must be at least 2 characters long.'));
        }

        if (mb_strlen($cat_name) > 32) {
            $form_state->setErrorByName('cat_name', $this->t('The cat\'s name must not exceed 32 characters.'));
        }
    }

    /**
     * {@inheritdoc}
     */
    public function submitForm(array &$form, FormStateInterface $form_state) {
        // The form submission is performed via submitFormAjax.    
    }

}

