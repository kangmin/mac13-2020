<?php

/**
 * @file
 * Contains \Drupal\bto_sgos_data\Form\BtoSgosDataForm.
 */

namespace Drupal\sym_sgos_data\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Render\Element;
use Drupal\sym_sgos_data\BcryptSaltGenerator;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class BtoSgosDataForm
 * @package Drupal\bto_sgos_data\Form
 */
class SymSgosData extends FormBase {
  private $saltGenerator;
  public static function create(ContainerInterface $container)
  {
    $saltGenerator = $container->get('sym_sgos_data.get_bscrypt_salt');
    return new static($saltGenerator);
  }
  public function __construct(BcryptSaltGenerator $saltGenerator)
  {
    $this->saltGenerator = $saltGenerator;
  }

  /**
   * @return string
   *
   */
  public function getFormId() {
    return 'sym_sgos_data';
  }
  /*
  //this works as direct service call
  public function getSalt($len){
    //Here uses service SymSGOSDataSaltGenerator()
    $salt = new BcryptSaltGenerator();
    $salt->_btoGetSalt($len);
  }
  */

  /**
   * @param array $form
   * @param FormStateInterface $form_state
   * @return array
   */
  public function buildForm(array $form, FormStateInterface $form_state) {

    if ($form_state->has('page_num') && $form_state->get('page_num') == 2) {
      return self::BtoSgosDataOutput($form, $form_state);
    }

    $form_state->set('page_num', 1);

    $form['description'] = [
      '#type' => 'item',
      '#title' => $this->t('Bcrypt Password Generator'),
    ];

    $form['username'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Console Username'),
      '#required' => TRUE,
      '#maxlength' => 255,
      '#default_value' => $form_state->getValue('username', ''),
    ];
    $form['serial_number'] = [
      '#type' => 'textfield',
      '#title' => t('Serial Number'),
      '#required' => TRUE,
      '#maxlength' => 10,
      '#description' => 'Enter a string with 10 digits.',
      '#default_value' => $form_state->getValue('serial_number', ''),
    ];

    $form['console_field'] = [
      '#type' => 'fieldset',
      '#title' => t('Authentication'),
      '#maxlength' => 285,
      '#collapsible' => TRUE,
      '#collapsed' => FALSE,
    ];
    $form['console_field']['authentication_tokenizer'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Authentication Tokenizer'),
      '#required' => TRUE,
      '#maxlength' => 255,
      '#default_value' => $form_state->getValue('authentication_tokenizer', ''),
    ];
    $form['console_field']['password']['password_confirm_wrapper'] = [
      '#type' => 'container',
      '#title' => $this->t('Generate Console Password'),
      '#states' => [
        'visible' => [
          ':input[name="console_password"]' => ['checked' => FALSE],
        ],
      ],
    ];
    $form['console_field']['password']['password_confirm_wrapper']['provided'] = [
      '#type' => 'password_confirm',
      '#size' => 25,
      '#required' => TRUE,
    ];

    $form['actions'] = [
      '#type' => 'actions',
    ];

    $form['actions']['next'] = [
      '#type' => 'submit',
      '#button_type' => 'primary',
      '#value' => $this->t('Generate User Data'),
      '#submit' => ['::BtoSgosDataSubmit'],
      '#validate' => ['::BtoSgoDataValidate'],
    ];

    return $form;
  }
  /**
   * @param $len
   * Generate 8 random characters salt
   * @return ASCII string
   * Generate char salt randomly
   */
  private function _bto_sgos_data_get_salt($len){
    $salt = '';
    for($i = 0; $i < $len; $i++){
      $num = rand(0, 2);
      if($num == 0){
        $salt .= chr(rand(48, 57)); // ASCII for numbers
      }
      elseif($num == 1){
        $salt .= chr(rand(65, 90)); // ASCII for capital case letters
      }
      else{
        $salt .= chr(rand(97, 122)); // ASCII for lower case letters
      }
    }
    return $salt;
  }

  /**
   * @param array $form
   * @param FormStateInterface $form_state
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $page_values = $form_state->get('page_values');

    $this->messenger()->addMessage($this->t('Thank you for using the User Data Generator.'));

  }

  /**
   * Provides custom validation handler.
   *
   * @param array $form
   *   An associative array containing the structure of the form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   */
  public function BtoSgoDataValidate(array &$form, FormStateInterface $form_state) {
      $serial = $form_state->getValue(['serial_number']);
      if ((strlen($serial) < 10 || strlen($serial) > 10) || (!preg_match("/^\d{10}$/", $serial))) {
        $form_state->setErrorByName('serial', t('Serial Number: Enter a string with 10 digits.'));
      }
  }

  /**
   * Provides custom submission handler for Bto Sgos Data mainpage.
   *
   * @param array $form
   *   An associative array containing the structure of the form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   */
  public function BtoSgosDataSubmit(array &$form, FormStateInterface $form_state) {
    $form_state
      ->set('page_values', [

        'username' => $form_state->getValue('username'),
        'serial_number' => $form_state->getValue('serial_number'),
        'console_password' => $form_state->getValue('console_password'),
        'authentication_tokenizer' => $form_state->getValue('authentication_tokenizer'),
      ])
      ->set('page_num', 2)
      ->setRebuild(TRUE);
  }

  /**
   * @param array $form
   * @param FormStateInterface $form_state
   * @return array
   */
  public function BtoSgosDataOutput(array &$form, FormStateInterface $form_state) {

    //generte output here.
    $username                 = $form_state->getValue('username');
    $serial_number            = $form_state->getValue('serial_number');
    $console_password         = $form_state->getValue('console_password');
    $authentication_tokenizer = $form_state->getValue('authentication_tokenizer');

    $metaData = array('ICW_Params' =>
      array(
        'BC_SerialNumber' => $serial_number,
        'BC_AdminUsername' => $username
      ));

    //Bcrypt hash with 22 char salt
    //getSalt through injected Dependency service BcryptSaltGenerator
    $salt1 = $this->saltGenerator->_btoGetSalt(22);
    $salt2 = $this->saltGenerator->_btoGetSalt(22);
    //getSalt direct dependency
    //$salt1 = $this->_bto_sgos_data_get_salt(22);
    //$salt2 = $this->_bto_sgos_data_get_salt(22);
    //$salt1 = $this->_bto_sgos_data_get_salt(22);
    //$salt2 = $this->_bto_sgos_data_get_salt(22);
    $round = 12;
    $metaData['ICW_Params']['BC_ConsolePassword'] = crypt($console_password, "\$2a\$$round\$$salt1\$");
    $metaData['ICW_Params']['BC_AuthenticationTokenizer'] = crypt($authentication_tokenizer, "\$2a\$$round\$$salt2\$");

    $jsondata = json_encode($metaData);
    $jsondata = stripslashes($jsondata);

    $mdata = $jsondata;

  $save_mode = FILE_EXISTS_RENAME;
  //filename needs to be randomly generated lest conflict
  $file_name = '';
  $file_sign = date('Ymdhis', time());
  $json_obj = $form_state->getValue('username') . '-' . $file_sign.'.txt';

  if(isset($json_obj) && (!empty($json_obj))) {
    //$json_obj = $form_state['values']['username'] .'.txt';
    $filepath = 'public://metadata/' .$json_obj;
    $file = file_save_data($mdata, $filepath, $save_mode);
    $file_name = $GLOBALS['base_url'] . '/sites/default/files/metadata/' . $json_obj;
  }
    $ofid = db_query("SELECT fid FROM {file_managed} WHERE uri = :path", array(':path' => $filepath))->fetchField();
    if (isset($ofid)){
      $_SESSION['ofid'] = $ofid;
      $ofile =\Drupal\file\Entity\File::load($ofid);

      $_SESSION['ofid_path'] = $ofile->uri;
    }

    $file_content = file_get_contents($file_name);
    $content = '';
    $content .= '<p>Your file is ready for download. Click "<strong>Download File</strong>".</p>';
    $content .= '<div class=bcrypt_output_container><code>' . $mdata . '</code></div>';

    $content .= '<h4><strong>File name</strong>: ' . $json_obj . '</h4>
                 <a href="' . $file_name . '" download><div class="file-download">Download File</div></a><br/>';

    $form['intro'] = ['#markup' => $content];
    //$output[]['#attached']['library'][] = 'sym_sgos_data/code-styling';
    $form['#attached']['library'][] = 'sym_sgos_data/code-styling';
    $form['back'] = [
      '#type' => 'submit',
      '#value' => $this->t('Back'),
      // Custom submission handler for 'Back' button.
      '#submit' => ['::BtoSgosDataBack'],

      '#limit_validation_errors' => [],
    ];

    $form['submit'] = [
      '#type' => 'submit',
      '#button_type' => 'primary',
      '#value' => $this->t('Done'),
    ];

    return $form;
  }

  /**
   * Provides custom submission handler for 'Back' button (page 2).
   *
   * @param array $form
   *   An associative array containing the structure of the form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   */
  public function BtoSgosDataBack(array &$form, FormStateInterface $form_state) {
    $form_state
      // Restore values for the first step.
      ->setValues($form_state->get('page_values'))
      ->set('page_num', 1)
      ->setRebuild(TRUE);
  }

}
