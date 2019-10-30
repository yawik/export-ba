<?php

/**
 * YAWIK Export BA
 *
 * @filesource
 * @copyright 2019 CROSS Solution <https://www.cross-solution.de>
 * @license MIT
 */

declare(strict_types=1);
namespace ExportBA\Controller\Plugin;

use ExportBA\Options\AaOptions;
use Interop\Container\ContainerInterface;
use Zend\View\Renderer\PhpRenderer;

/**
 * Factory for \ExportBA\Controller\Plugin\AaXml
 *
 * @author Mathias Gelhausen <gelhausen@cross-solution.de>
 * TODO: write tests
 */
class AaXmlFactory
{
    public function __invoke(
        ContainerInterface $container,
        ?string $requestedName = null,
        ?array $options = null
    ): AaXml {

        $renderer = new PhpRenderer();
        $resolver = new \Zend\View\Resolver\TemplateMapResolver(
            $container->get('config')['view_manager']['template_map']
        );
        $renderer->setResolver($resolver);
        $renderer->setHelperPluginManager($container->get('ViewHelperManager'));

        $aaOptions = $container->get(AaOptions::class);

        return new AaXml(
            $renderer,
            $aaOptions->getSupplierId($options['name']),
            $aaOptions->getPartnerNr($options['name']),
            $aaOptions->getTemplate($options['name']),
            $aaOptions->getCachePath() . DIRECTORY_SEPARATOR . $options['name']
        );
    }
}
