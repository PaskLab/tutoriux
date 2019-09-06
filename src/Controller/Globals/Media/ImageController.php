<?php

namespace App\Controller\Globals\Media;

use Symfony\Component\HttpFoundation\Request,
    Symfony\Component\HttpFoundation\JsonResponse;

use App\Library\BaseController,
    App\Entity\Media\Media,
    App\Form\Globals\Media\ImageType;

/**
 * Class ImageController
 * @package App\Controller\Component\Media
 */
class ImageController extends BaseController
{
    /**
     * Edit image detail
     *
     * @param Request $request
     * @return JsonResponse
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     */
    public function editAction(Request $request)
    {
        if ($request->isXmlHttpRequest()) {

            $id = ($request->query->has('mediaId')) ? $request->query->get('mediaId') : $request->request->get('mediaId');

            $media = $this->getEm()->getRepository('MediaBundle:Media')->find($id);

            if (!$media) {
                throw $this->createNotFoundException('Unable to find the media');
            }

            if ($media->isLocked()) {
                return new JsonResponse(['error' => 'locked']);
            }

            $form = $this->createForm(ImageType::class, $media);

            if ("POST" == $request->getMethod()) {

                $form->handleRequest($request);

                if ($form->isValid()) {
                    $this->getEm()->persist($media);

                    $this->getEm()->flush();

                    $this->get('system.router_invalidator')->invalidate();
                }
            }

            $explode = explode('/', $media->getMediaPath());
            $realName = array_pop($explode);

            return new JsonResponse(array(
                'html' => $this->renderView('MediaBundle:Media/Manager/component:_image_edit.html.twig', array(
                    'form' => $form->createView(),
                    'media' => $media,
                    'fileExtension' => MediaController::guessExtension($media->getMediaPath()),
                    'realName' => $realName
                ))
            ));
        }

        return new JsonResponse();
    }

    /**
     * @param Request $request
     * @return JsonResponse
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     */
    public function resizeAction(Request $request)
    {
        if ($request->isXmlHttpRequest()
            && $request->query->has('mediaId')
            && $request->query->has('width')
            && $request->query->has('height')
            && $request->query->has('resize')) {

            $media = $this->getEm()->getRepository('MediaBundle:Media')->find($request->query->get('mediaId'));

            if (!$media) {
                throw $this->createNotFoundException('Unable to find the media');
            }

            if ($media->isLocked()) {
                return new JsonResponse(['error' => 'locked']);
            }

            if ('true' == $request->query->get('resize')) {

                $media->setResizeWidth($request->query->get('width'));
                $media->setResizeHeight($request->query->get('height'));
                $media->setCropJson(null);
                $media->setCropWidth(null);
                $media->setCropHeight(null);
                $this->getEm()->flush();

                $this->get('liip_imagine.cache.manager')->remove($media->getWebPath('media'));
                // Force liip imagine to create base image
                try {
                    $this->get('media.filter_manager')->applyFilter($media->getMediaPath(), 'media');
                } catch (\Exception $exception) {
                    $this->addFlashError($this->get('translator')->trans('flash.error.modifying_image', [], 'media_manager'));
                }

                $newSize = filesize($this->container->getParameter('kernel.root_dir').'/../web/media/cache/media'.$media->getMediaPath());
                $media->setSize($newSize);
                $this->getEm()->flush();
            }

            $explode = explode('/', $media->getMediaPath());
            $realName = array_pop($explode);

            return new JsonResponse(array(
                'html' => $this->renderView('MediaBundle:Media/Manager/component:_image_resize.html.twig', array(
                        'media' => $media,
                        'fileExtension' => MediaController::guessExtension($media->getMediaPath()),
                        'realName' => $realName
                    )),
                'maxWidth' => $media->getWidth(),
                'maxHeight' => $media->getHeight(),
                'width' => $media->getResizeWidth(),
                'height' => $media->getResizeHeight()
            ));
        }

        return new JsonResponse();
    }

    /**
     * @param Request $request
     * @return JsonResponse
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     */
    public function cropAction(Request $request)
    {
        if ($request->isXmlHttpRequest()
            && $request->query->has('mediaId')
            && $request->query->has('width')
            && $request->query->has('height')
            && $request->query->has('crop')) {

            $media = $this->getEm()->getRepository('MediaBundle:Media')->find($request->query->get('mediaId'));

            if (!$media) {
                throw $this->createNotFoundException('Unable to find the media');
            }

            if ($media->isLocked()) {
                return new JsonResponse(['error' => 'locked']);
            }

            if ('true' == $request->query->get('crop')) {

                if ('' == $request->query->get('width')) {
                    $media->setCropJson(null)
                        ->setCropWidth(null)
                        ->setCropHeight(null);
                } else {
                    $media->setCropJson(json_encode([
                        'x1' => $request->query->get('x1'),
                        'y1' => $request->query->get('y1'),
                        'x2' => $request->query->get('x2'),
                        'y2' => $request->query->get('y2')
                    ]))
                        ->setCropWidth(round($request->query->get('width') * $media->getResizeWidth()))
                        ->setCropHeight(round($request->query->get('height') * $media->getResizeHeight()));
                }

                $this->getEm()->flush();

                $this->get('liip_imagine.cache.manager')->remove($media->getWebPath('media'));
                // Force liip imagine to create base image
                try {
                    $this->get('media.filter_manager')->applyFilter($media->getMediaPath(), 'media');
                } catch (\Exception $exception) {
                    $error = $this->get('translator')->trans('flash.error.modifying_image', [], 'media_manager');
                }

                $newSize = filesize($this->container->getParameter('kernel.root_dir').'/../web/media/cache/media'.$media->getMediaPath());
                $media->setSize($newSize);
                $this->getEm()->flush();
            }

            $explode = explode('/', $media->getMediaPath());
            $realName = array_pop($explode);

            return new JsonResponse(array(
                'html' => $this->renderView('MediaBundle:Media/Manager/component:_image_crop.html.twig', array(
                        'media' => $media,
                        'fileExtension' => MediaController::guessExtension($media->getMediaPath()),
                        'realName' => $realName
                )),
                'oWidth' => $media->getResizeWidth(),
                'oHeight' => $media->getResizeHeight(),
                'crop' => json_decode($media->getCropJson(), true)
            ));
        }

        return new JsonResponse();
    }

    /**
     * @param $id
     * @param Request $request
     * @return JsonResponse
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     */
    public function updateImageAction($id, Request $request)
    {
        /** @var Media $image */
        $image = $this->getEm()->getRepository('MediaBundle:Media')->find($id);

        if (!$image) {
            throw $this->createNotFoundException('Unable to find the Media Entity');
        }

        $absoluteMediaPath = $this->get('kernel')->getProjectDir() . '/public' . $image->getMediaPath();

        file_put_contents($absoluteMediaPath, file_get_contents($request->get('image')));

        // Update image format
        list($width, $height, $type, $attr) = getimagesize($absoluteMediaPath);

        $image->setWidth($width);
        $image->setHeight($height);

        $this->getEm()->persist($image);
        $this->getEm()->flush();

        //The imagine cache needs to be cleared because the image keep the same filename
        $cacheManager = $this->container->get('liip_imagine.cache.manager');

        foreach ($this->container->getParameter('liip_imagine.filter_sets') as $filter => $value ) {
            $cacheManager->remove(substr($image->getMediaPath(), 1), $filter);
        }

        return new JsonResponse(array());
    }
}
