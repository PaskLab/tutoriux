<?php

namespace App\Controller\Site;

use App\Library\BaseController;
use Symfony\Component\HttpFoundation\Response;
use Tutoriux\DoctrineBehaviorsBundle\ORM\Metadatable\MetadatableGetter;

/**
 * Class MetadataController
 * @package App\Controller\Site
 */
class MetadataController extends BaseController
{
    /**
     * @param MetadatableGetter $metadatableGetter
     * @param $metaName
     * @param bool $ogMeta
     * @param bool $forceEmpty
     * @return Response
     */
    public function metadata(MetadatableGetter $metadatableGetter, $metaName, $ogMeta = false, $forceEmpty = false)
    {
        $response = new Response();
        $response->setPublic();
        $response->setSharedMaxAge(86400); // 1 day

        $element = $this->getApplicationCore()->getCurrentElement();

        $value = $metadatableGetter->getMetadata($element, $metaName);

        if (!$value && !$forceEmpty) {
            $parameter = sprintf('tutoriux.metadata.%s', $metaName);

            if ($this->container->hasParameter($parameter)) {
                $value = $this->container->getParameter($parameter);
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