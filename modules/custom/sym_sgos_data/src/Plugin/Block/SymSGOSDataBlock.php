<?php

namespace Drupal\sym_sgos_data\Plugin\Block;
use Drupal\Core\Block\BlockBase;
/**
* Provides a user details block. *
* @Block(
* id = "hello_block",
* admin_label = @Translation("Hello!") *)
*/
class SymSGOSDataBlock extends BlockBase {
  /**
   * {@inheritdoc}
   */
  public function build() {
    return array(
'#markup' => $this->t("Hello World!"), );
} }
