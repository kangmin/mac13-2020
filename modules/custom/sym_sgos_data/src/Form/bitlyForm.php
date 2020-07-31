<?php
/**
* source: https://www.specbee.com/blogs/how-integrate-drupal-8-bitly-url-shortening
* bitlyForm.php
*/
namespace Drupal\bitly\Form;
use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
/**
* Class bitlyForm.
*
* @package Drupal\bitly\Form
*/
class bitlyForm extends ConfigFormBase {
 /**
  * {@inheritdoc}
  */
 protected function getEditableConfigNames() {
return ['bitly.shorten_url'];
}
 /**
  * {@inheritdoc}
  */
 public function getFormId() {
 return 'bitly_shorten_url_configuration_form';
 }
 /**
  * {@inheritdoc}
  */
 public function buildForm(array $form, FormStateInterface $form_state) {
  $config = $this->config('bitly.shorten_url');
   $form['configuration'] = [
     '#type' => 'fieldset',
     '#title' => $this->t('Configuration'),
   ];
   $form['configuration']['login_name'] = [
     '#type' => 'textfield',
     '#title' => $this->t('Login Name'),
     '#default_value' => $config->get('login_name'),
     '#required' => TRUE,
   ];
   $form['configuration']['app_key'] = [
     '#type' => 'textfield',
     '#title' => $this->t('Application Key'),
     '#default_value' => $config->get('app_key'),
     '#required' => TRUE,
   ];

   return parent::buildForm($form, $form_state);
 }
 /**
  * {@inheritdoc}
  */
 public function submitForm(array &$form, FormStateInterface $form_state) {
   $this->config('bitly.shorten_url')
     ->set('login_name', trim($form_state->getValue('login_name')))
     ->set('app_key', trim($form_state->getValue('app_key')))
     ->save();
   parent::submitForm($form, $form_state);
 }
}
