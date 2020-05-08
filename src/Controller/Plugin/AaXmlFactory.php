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

use ExportBA\Entity\FileQueue;
use ExportBA\Entity\JobMetaData;
use ExportBA\Options\AaOptions;
use Interop\Container\ContainerInterface;
use Laminas\View\Renderer\PhpRenderer;

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
        $resolver = new \Laminas\View\Resolver\TemplateMapResolver(
            $container->get('config')['view_manager']['template_map']
        );
        $renderer->setResolver($resolver);
        $renderer->setHelperPluginManager($container->get('ViewHelperManager'));

        $aaOptions = $container->get(AaOptions::class);

        /** @var \ExportBA\Repository\FileQueuesRepository $queues */
        $queues = $container->get('repositories')->get(FileQueue::class);
        $queue = $queues->findByName($options['name']);
        if (!$queue) {
            $queue = $queues->create(['name' => $options['name']], true);
        }

        return new AaXml(
            $renderer,
            $queue,
            $aaOptions->getSupplierId($options['name']),
            $aaOptions->getPartnerNr($options['name']),
            $aaOptions->getTemplate($options['name']),
            $aaOptions->getCachePath() . DIRECTORY_SEPARATOR . $options['name'],
            $container->get('repositories')->get(JobMetaData::class)
        );
    }
}
