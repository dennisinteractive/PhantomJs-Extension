<?php

namespace Behat\PhantomJsExtension\ServiceContainer\Driver;

use Behat\MinkExtension\ServiceContainer\Driver\Selenium2Factory;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use WebDriver\ServiceFactory;

class PhantomJsFactory extends Selenium2Factory
{
    /**
     * {@inheritdoc}
     */
    public function getDriverName()
    {
        return 'phantomjs';
    }

    /**
     * {@inheritdoc}
     */
    public function configure(ArrayNodeDefinition $builder)
    {
        $builder
            ->children()
                ->scalarNode('browser')->defaultValue('%mink.browser_name%')->end()
                ->append($this->getCapabilitiesNode())
                ->scalarNode('wd_host')->defaultValue('http://localhost:8643/wd/hub')->end()
                ->scalarNode('wd_port')->defaultValue(8643)->end()
                ->scalarNode('bin')->defaultValue('/usr/local/bin/phantomjs')->end()
                ->append($this->getCurlNode())->end()
            ->end()
        ;
    }

    /**
     * Get Curl node definition.
     *
     * @return ArrayNodeDefinition
     */
    protected function getCurlNode()
    {
      $node = new ArrayNodeDefinition('curl_options');

      $node
        ->addDefaultsIfNotSet()
        ->normalizeKeys(false)
        ->children()
            ->scalarNode('CURLOPT_CONNECTTIMEOUT')->end()
            ->scalarNode('CURLOPT_TIMEOUT')->end()
        ->end();

      return $node;
    }

    /**
     * {@inheritdoc}
     */
    public function buildDriver(array $config)
    {
        if (!class_exists('Behat\Mink\Driver\Selenium2Driver')) {
            throw new \RuntimeException(sprintf(
                'Install MinkSelenium2Driver in order to use %s driver.',
                $this->getDriverName()
            ));
        }

        $extraCapabilities = $config['capabilities']['extra_capabilities'];
        unset($config['capabilities']['extra_capabilities']);

        if (getenv('TRAVIS_JOB_NUMBER')) {
            $guessedCapabilities = array(
                'tunnel-identifier' => getenv('TRAVIS_JOB_NUMBER'),
                'build' => getenv('TRAVIS_BUILD_NUMBER'),
                'tags' => array('Travis-CI', 'PHP '.phpversion()),
            );
        } elseif (getenv('JENKINS_HOME')) {
            $guessedCapabilities = array(
                'tunnel-identifier' => getenv('JOB_NAME'),
                'build' => getenv('BUILD_NUMBER'),
                'tags' => array('Jenkins', 'PHP '.phpversion(), getenv('BUILD_TAG')),
            );
        } else {
            $guessedCapabilities = array(
                'tags' => array(php_uname('n'), 'PHP '.phpversion()),
            );
        }

        // Setup custom Curl service.
        ServiceFactory::getInstance()->setServiceClass('service.curl', 'Behat\PhantomJsExtension\Service\CurlService');
        $curl_service = ServiceFactory::getInstance()->getService('service.curl');
        if (isset($config['curl_options']['CURLOPT_TIMEOUT'])) {
          $curl_service->setTimeout($config['curl_options']['CURLOPT_TIMEOUT']);
        }
        if (isset($config['curl_options']['CURLOPT_CONNECTTIMEOUT'])) {
          $curl_service->setConnectTimeout($config['curl_options']['CURLOPT_CONNECTTIMEOUT']);
        }

        return new Definition('Behat\PhantomJsExtension\Driver\PhantomJsDriver', array(
            $config['browser'],
            array_replace($extraCapabilities, $guessedCapabilities, $config['capabilities']),
            $config['wd_host'],
            $config['wd_port'],
            $config['bin']
        ));
    }
}
