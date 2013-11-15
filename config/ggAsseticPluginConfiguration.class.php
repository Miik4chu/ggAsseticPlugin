<?php

/**
 * ggAsseticPluginConfiguration configures application to use Assetic.
 *
 * @package    ggAsseticPlugin
 * @subpackage configuration
 * @author     Gunther Groenewege <gunther@groenewege.com>
 * @version    1.0.0
 */
class ggAsseticPluginConfiguration extends sfPluginConfiguration
{
  public function initialize()
  {
    $this->dispatcher->connect('routing.load_configuration', array('ggAsseticListeners', 'loadAsset'));
  }
}
