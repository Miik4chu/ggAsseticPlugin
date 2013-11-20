<?php
use Assetic\Filter\UglifyCssFilter;
use Assetic\Filter\Yui\CssCompressorFilter;
use Assetic\Filter\Yui\JsCompressorFilter;

/**
 * sseticBuildTask compiles css and js files thru symfony cli task system.
 *
 * @package    ggAsseticPlugin
 * @subpackage tasks
 * @author     Gunther Groenewege <gunther@groenewege.com>
 * @version    1.0.0
 */
class asseticBuildTask extends sfBaseTask
{
    /**
     * @see sfTask
     */
    protected function configure()
    {
        $this->addArguments(array(
            new sfCommandArgument('application', sfCommandArgument::REQUIRED, 'The application name'),
        ));

        $this->addOptions(array(
            new sfCommandOption(
                'type', null, sfCommandOption::PARAMETER_OPTIONAL,
                'The file types to optimize', 'all'
            ),
            new sfCommandOption(
                'env', null, sfCommandOption::PARAMETER_REQUIRED,
                'The environment', 'prod'
            )
        ));

        $this->namespace = 'assetic';
        $this->name = 'build';
        $this->briefDescription = 'Recompiles javascript / css files';
        $this->detailedDescription = <<<EOF
The [assetic:build|INFO] task combines javascript and css files and minifies them. 
type options = all/css/javascript
Call it with:

  [php symfony assetic:build frontend --type=all|INFO]
EOF;
    }

    /**
     * @see sfTask
     */
    protected function execute($arguments = array(), $options = array())
    {
        if ($options['type'] == 'all' || $options['type'] == 'javascript') {
            $this->combineJavascript($arguments, $options);
        }
        if ($options['type'] == 'all' || $options['type'] == 'css') {
            $this->combineStylesheet($arguments, $options);
        }
    }

    public function combineJavascript($arguments = array(), $options = array())
    {
        $config = sfConfig::get('app_gg_assetic_javascript');

        $context = sfContext::createInstance($this->configuration);
        $this->configuration->loadHelpers('Partial');

        $am = new Assetic\AssetManager();
        $filters = [new JsCompressorFilter(sfConfig::get('sf_root_dir') . '/bin/yuicompressor-2.4.8.jar')];
        $writer = new Assetic\AssetWriter(sfConfig::get('sf_web_dir') . '/js');

        foreach ($config as $name => $script) {
            $references = [];

            if (isset($script['files'])) {
                foreach ($script['files'] as $file) {
                    $file_ref = str_replace(['.', '/', '-'], '_', $file);
                    $am->set($file_ref, new Assetic\Asset\FileAsset(sfConfig::get('sf_web_dir') . '/js/' . $file));
                    $references[] = new Assetic\Asset\AssetReference($am, $file_ref);
                }
            }

            if (isset($script['partials'])) {
                foreach ($script['partials'] as $partial) {
                    $partial_ref = str_replace('/', '_', $partial);
                    $am->set($partial_ref, new Assetic\Asset\StringAsset(get_partial($partial, array('routing' => $this->getProductionRouting($arguments['application'])))));
                    $references[] = new Assetic\Asset\AssetReference($am, $partial_ref);
                }
            }

            $coll = new Assetic\Asset\AssetCollection($references, $filters);

            if (isset($script['version'])) {
                $filename = '_' . $name . '.js';
                $coll->setTargetPath($filename);
                $writer->writeAsset($coll);
                $this->logSection('build', sprintf(' - javascript file %s', $filename));
            }

        }

    }

    public function combineStylesheet($arguments = array(), $options = array())
    {
        $config = sfConfig::get('app_gg_assetic_css');

        $context = sfContext::createInstance($this->configuration);
        $this->configuration->loadHelpers('Partial');

        $am = new Assetic\AssetManager();
        $filters = array(new CssCompressorFilter(sfConfig::get('sf_root_dir') . '/bin/yuicompressor-2.4.8.jar'));
        $writer = new Assetic\AssetWriter(sfConfig::get('sf_web_dir') . '/css');

        foreach ($config as $name => $style) {
            $references = [];

            if (isset($style['files'])) {
                foreach ($style['files'] as $file) {
                    $file_ref = str_replace(['.', '-', '/'], '_', $file);
                    $am->set($file_ref, new Assetic\Asset\FileAsset(sfConfig::get('sf_web_dir') . '/css/' . $file));
                    $references[] = new Assetic\Asset\AssetReference($am, $file_ref);
                }
            }

            if (isset($style['partials'])) {
                foreach ($style['partials'] as $partial) {
                    $partial_ref = str_replace('/', '_', $partial);
                    $am->set($partial_ref, new Assetic\Asset\StringAsset(get_partial($partial, array('routing' => $this->getProductionRouting($arguments['application'])))));
                    $references[] = new Assetic\Asset\AssetReference($am, $partial_ref);
                }
            }

            $coll = new Assetic\Asset\AssetCollection($references, $filters);

            if (isset($style['version'])) {
                $filename = '_' . $name . '.css';
                $coll->setTargetPath($filename);
                $writer->writeAsset($coll);
                $this->logSection('build', sprintf(' - css file %s', $filename));
            }

        }

    }

    protected function getProductionRouting($application = null)
    {
        $routing = $this->getRouting();
        $routingOptions = $routing->getOptions();
        if ($application == 'backend') {
            $routingOptions['context']['prefix'] = '/backend.php';
        }
        $routing->initialize($this->dispatcher, $routing->getCache(), $routingOptions);
        return $routing;
    }
}

