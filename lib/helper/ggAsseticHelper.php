<?php

/**
 * ggAsseticHelper handles the Assetic js and css.
 *
 * @package    ggAsseticPlugin
 * @subpackage helper
 * @author     Gunther Groenewege <gunther@groenewege.com>
 * @version    1.0.0
 */
use_helper('Asset');

function _gg_assetic_hash($config)
{
    if (!is_array($config['files'])) {
        $config['files'] = [];
    }

    return substr(sha1($config['version'] . implode(';', $config['files']) . sfConfig::get('sf_root_dir')), 0, 7);
}

function _gg_is_debug($config)
{
    return sfConfig::get('sf_debug', false) || !(isset($config['version']) && $config['version'] > 0);
}

function _gg_assetic_get_stylesheet_options($options = null) {
    if (!is_array($options)) {
        return array('media' => 'all');
    }
    return $options;
}

function gg_javascript_path($name)
{
    $config = sfConfig::get('app_gg_assetic_javascript');
    return _gg_is_debug($config[$name]) ? url_for('@asset_js?name=' . $name) : '_' . $name . '.js?' . _gg_assetic_hash($config[$name]);
}

function gg_use_javascript($js)
{
    use_javascript(gg_javascript_path($js));
}

function gg_include_javascript($name) {
    echo javascript_include_tag(gg_javascript_path($name));
}

function gg_use_stylesheet($css, $options = null)
{
    $config = sfConfig::get('app_gg_assetic_css');
    $options = _gg_assetic_get_stylesheet_options($options);

    if (_gg_is_debug($config[$css])) {
        use_stylesheet(url_for('@asset_css?name=' . $css), '', $options);
    } else {
        use_stylesheet('_' . $css . '.css?' . _gg_assetic_hash($config[$css]), '', $options);
    }
}

function gg_include_stylesheet($css, $options = null)
{
    $config = sfConfig::get('app_gg_assetic_css');
    $options = _gg_assetic_get_stylesheet_options($options);

    if (_gg_is_debug($config[$css])) {
        echo stylesheet_tag(url_for('@asset_css?name=' . $css), $options);
    } else {
        echo stylesheet_tag('_' . $css . '.css?' . _gg_assetic_hash($config[$css]), $options);
    }
}
