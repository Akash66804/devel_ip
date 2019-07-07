<?php

namespace Drupal\devel_ip\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Configure devel_ip settings for this site.
 */
class DevelIPForm extends ConfigFormBase {
  /**
 * @var string Config settings */
  const SETTINGS = 'devel_ip.settings';

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'devel_ip_admin_setting';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      static::SETTINGS,
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config(static::SETTINGS);
    $name_field = $form_state->get('num_of_ips');
    $form['#tree'] = TRUE;
    // Get all ips in devel_ip.settings config file.
    $ips = \Drupal::configFactory()->get('devel_ip.settings')->get('ip');
    $form['ip_fieldset'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('IP address'),
      '#default_value' => $config->get('ip'),
      '#prefix' => "<div id='ip-fieldset-wrapper'>",
      '#suffix' => '</div>',
    ];
    if (empty($name_field)) {
      $name_field = $form_state->set('num_of_ips', count($ips));
    }
    for ($i = 1; $i <= $form_state->get('num_of_ips'); $i++) {
      $form['ip_fieldset']['ip_address'][$i] = [
        '#type' => 'textfield',
        '#title' => $this->t('Add IP address to allow access'),
        '#default_value' => $ips[$i],
        '#prefix' => "<div ><legend><span>IP address {$i}</span></legend></div>",
      ];
    }
    $form['ip_fieldset']['actions'] = [
      '#type' => 'actions',
    ];
    $form['ip_fieldset']['actions']['add_ip'] = [
      '#type' => 'submit',
      '#value' => t('Add one more'),
      '#submit' => ['::addOne'],
      '#ajax' => [
        'callback' => '::addmoreCallback',
        'wrapper' => "ip-fieldset-wrapper",
      ],
    ];
    if ($form_state->get('num_of_ips') > 1) {
      $form['ip_fieldset']['actions']['remove_ip'] = [
        '#type' => 'submit',
        '#value' => t('Remove one'),
        '#submit' => ['::removeCallback'],
        '#ajax' => [
          'callback' => '::addmoreCallback',
          'wrapper' => "ip-fieldset-wrapper",
        ],
      ];
    }
    $form_state->setCached(FALSE);
    return parent::buildForm($form, $form_state);
  }

  /**
   *
   */
  public function addmoreCallback(array &$form, FormStateInterface $form_state) {
    $name_field = $form_state->get('num_of_ips');
    return $form['ip_fieldset'];
  }

  /**
   *
   */
  public function addOne(array &$form, FormStateInterface $form_state) {
    $name_field = $form_state->get('num_of_ips');
    $add_button = $name_field + 1;
    $form_state->set('num_of_ips', $add_button);
    $form_state->setRebuild();
  }

  /**
   *
   */
  public function removeCallback(array &$form, FormStateInterface $form_state) {
    $name_field = $form_state->get('num_of_ips');
    if ($name_field > 1) {
      $remove_button = $name_field - 1;
      $form_state->set('num_of_ips', $remove_button);
    }
    $form_state->setRebuild();
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    //Array of all Ips
    $ip_address_arr = $form_state->getValue('ip_fieldset')['ip_address'];
    // print_r($ip_address_arr); 
    // echo '<br>';
     $last_ip = end($ip_address_arr);
    // echo $last_ip;
        
    for($i=1; $i<count($ip_address_arr); $i++)
    {
      if($last_ip == $ip_address_arr[$i])
      {
        // echo $last_ip;
        // echo $ip_address_arr[$i];
        // echo '<br>';
        $form_state->setErrorByName('ip_address', drupal_set_message(t('IP address already exists.'), 'error'));
       }  
     } //die();
    if (!empty($ip_address_arr)) {
      foreach ($ip_address_arr as $value) {
        if (!empty($value)) {
          // Validate Ips
          if (filter_var($value, FILTER_VALIDATE_IP) == FALSE) {
            $form_state->setErrorByName('ip_address', drupal_set_message(t('Enter a valid IP address.'), 'error'));
           // $form_state->setValue(['ip_fieldset', 'ip_address']) = "";

          }
        }
      }
    }
    // else {
    //   $form_state->setErrorByName('ip_addresss', t('Ip address field can not be blank.'));
    // }
    parent::validateForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $values = $form_state->getValue(['ip_fieldset', 'ip_address']);
    $this->configFactory->getEditable(static::SETTINGS)
      ->set('ip', $values)
      ->save();
    // Clear route caches  
    $router_builder = \Drupal::service('router.builder');
    $router_builder->rebuild();
    parent::submitForm($form, $form_state);
  }
}
