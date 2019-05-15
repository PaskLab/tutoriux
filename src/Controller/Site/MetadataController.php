<?php

namespace App\Controller\Site;

use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\Response;
use App\Library\BaseController;
use Tutoriux\DoctrineBehaviorsBundle\ORM\Metadatable\MetadatableGetter;

/**
 * Class MetadataController
 * @package App\Controller\Site
 */
class MetadataController extends BaseController
{
    /**
     * @param MetadatableGetter $metadatableGetter
     * @param ParameterBagInterface $parameterBag
     * @param $metaName
     * @param bool $ogMeta
     * @param bool $forceEmpty
     * @return Response
     */
    public function metadata(MetadatableGetter $metadatableGetter, ParameterBagInterface $parameterBag,
                             $metaName, $ogMeta = false, $forceEmpty = false)
    {
        $response = new Response();
        $response->setPublic();
        $response->setSharedMaxAge(86400); // 1 day

        $element = $this->getApplicationCore()->getCurrentElement();

        $value = $metadatableGetter->getMetadata($element, $metaName);

        if (!$value && !$forceEmpty) {
            $parameter = sprintf('tutoriux.metadata.%s', $metaName);

            if ($parameterBag->has($parameter)) {
                $value = $parameterBag->get($parameter);
            }
        }

        $view = $ogMeta ? 'site/core/og_meta.html.twig' : 'site/core/meta.html.twig';

        return $this->render(
            $view,
            [
                'element' => $element,
                'meta_name' => $metaName,
                'value' => $value
            ],
            $response
        );
    }
}