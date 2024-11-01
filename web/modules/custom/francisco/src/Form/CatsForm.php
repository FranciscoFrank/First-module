<?php 

namespace Drupal\francisco\Form;

use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\HtmlCommand;
use Drupal\Core\Ajax\InvokeCommand;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Messenger\MessengerInterface;
use Drupal\Component\Utility\EmailValidatorInterface;
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
     * The email validator service.
     *
     * @var \Drupal\Component\Utility\EmailValidatorInterface
     */
    protected $emailValidator;

    /**
     * Constructs a CatsForm object.
     *
     * @param \Drupal\Core\Messenger\MessengerInterface $messenger
     *   The messenger service.
     * @param \Drupal\Component\Utility\EmailValidatorInterface $email_validator
     *   The email validator service.
     */
    public function __construct(MessengerInterface $messenger, EmailValidatorInterface $email_validator) {
        $this->messenger = $messenger;
        $this->emailValidator = $email_validator;
    }

    /**
     * {@inheritdoc}
     */
    public static function create(ContainerInterface $container) {
        return new static(
            $container->get('messenger'),
            $container->get('email.validator')
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

        $form['success_message'] = [
            '#type' => 'markup',
            '#prefix' => '<div class="cats-form__messages">',
            '#suffix' => '</div>',
            '#markup' => '<div class="messages--success"></div>',
        ];

        $form['validation_message'] = [
            '#type' => 'markup',
            '#prefix' => '<div class="cats-form__validation-message">',
            '#suffix' => '</div>',
            '#markup' => '<div class="validation-message__cat_name"></div>
                        <div class="validation-message__cat_email"></div>',
        ];         

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

        $form['cat_email'] = [
            '#type' => 'email',
            '#title' => $this->t('Your email:'),
            '#description' => $this->t('Email must contain only latin letters, underscores, or hyphens.'),
            '#required' => TRUE,
            '#attributes' => [
                'placeholder' => $this->t('Enter your email'),
                'class' => ['cats-form__email-field'],
            ],
            '#ajax' => [
                'callback' => '::validateEmailAjax',
                'event' => 'change',
            ],
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

        if (mb_strlen($cat_name) < 2 || mb_strlen($cat_name) > 32) {
            $error_messages[] = $this->t('The cat\'s name must have a minimum of 2 characters and a maximum of 32 characters.');
        }

        $field = 'cat_name';
        $this->handleValidationMessages($response, $error_messages, $field);

        return $response;
    }

    /**
     * AJAX form for email validation.
     *
     * @param array $form
     * @param FormStateInterface $form_state
     * @return void
     */
    public function validateEmailAjax(array &$form, FormStateInterface $form_state) {
        $response = new AjaxResponse();
        $cat_email = $form_state->getValue('cat_email');
        $error_messages = [];
        
        if (!$this->emailValidator->isValid($cat_email)) {
            $error_messages[] = $this->t('The email is not valid. Example of the correct email: example@example.com');
        }

        $field = 'cat_email';
        $this->handleValidationMessages($response, $error_messages, $field);

        return $response;
    }

    /**
     * Display messages with a warning
     *
     * @param AjaxResponse $response
     * @param array $error_messages
     * @param string $field
     * @return void
     */
    public function handleValidationMessages(AjaxResponse $response, array $error_messages, string $field) {
        $underscore_pos = strpos($field, '_');
    
        if ($underscore_pos !== FALSE) {
            $field_name = substr($field, $underscore_pos + 1);
        } else {
            $field_name = $field;
        }

        $response->addCommand(new InvokeCommand('.cats-form__' . $field_name . '-field', 'removeClass', ['error'])); 
        $response->addCommand(new HtmlCommand('.cats-form__messages', ''));

        if (!empty($error_messages)) {
            $response->addCommand(new HtmlCommand(
                '.validation-message__' . $field, 
                '<div class="messages messages--warning">' . 
                implode('<br>', $error_messages) . 
                '</div>'
            ));
            $response->addCommand(new InvokeCommand('.cats-form__' . $field_name . '-field', 'addClass', ['warning']));
        } else {
            $response->addCommand(new HtmlCommand('.validation-message__' . $field, ''));
            $response->addCommand(new InvokeCommand('.cats-form__' . $field_name . '-field', 'removeClass', ['warning']));
        }
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
            $error_messages = $form_state->getErrors();
        
            foreach ($error_messages as $field => $message) {
                $underscore_pos = strpos($field, '_');

                if ($underscore_pos !== FALSE) {
                    $field_name = substr($field, $underscore_pos + 1);
                } else {
                    $field_name = $field;
                }

                $response->addCommand(new HtmlCommand(
                    '.validation-message__' . $field, 
                    '<div class="messages messages--error">' . 
                    implode('<br>', $error_messages) . 
                    '</div>'
                ));
    
                $response->addCommand(new InvokeCommand('.cats-form__' . $field_name . '-field', 'removeClass', ['warning']));
                $response->addCommand(new InvokeCommand('.cats-form__' . $field_name . '-field', 'addClass', ['error']));
            }
        } else {
            $this->submitForm($form, $form_state);

            $response->addCommand(new InvokeCommand('input[type="text"], input[type="email"]', 'val', ['']));
            $response->addCommand(new HtmlCommand(
                '.cats-form__messages', 
                '<div class="messages--success messages messages--status">'. 
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
        
        if (mb_strlen($cat_name) < 2 || mb_strlen($cat_name) > 32) {
            $form_state->setErrorByName('cat_name', $this->t('The cat\'s name must have a minimum of 2 characters and a maximum of 32 characters.'));
        }
    }

    /**
     * {@inheritdoc}
     */
    public function submitForm(array &$form, FormStateInterface $form_state) {
        // The form submission is performed via submitFormAjax.    
    }

}

