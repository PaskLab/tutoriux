<?php

namespace App\Controller\Globals\Media;

use Symfony\Component\Config\Definition\Exception\Exception,
    Symfony\Component\Form\FormError,
    Symfony\Component\HttpFoundation\JsonResponse,
    Symfony\Component\HttpFoundation\Request;

use App\Library\BaseController;
use App\Library\Globals\Media\MediaParserInterface;
use App\Library\Globals\Media\MediaFile;
use App\Entity\Media\Media;
use App\Form\Globals\Media\EmbedVideoType;
use App\Services\DoctrineInit;

/**
 * Class EmbedVideoController
 * @package App\Controller\Globals\Media
 */
class EmbedVideoController extends BaseController
{
    /**
     * Create a video from a url
     *
     * @param Request $request
     * @param DoctrineInit $doctrineInit
     * @return JsonResponse
     */
    public function createAction(Request $request, DoctrineInit $doctrineInit)
    {
        $t = $this->get('translator');

        if ("POST" !== $request->getMethod()) {
            throw new Exception('The request method must be post.');
        }

        $mediaParser = $this->get('media.parser');
        if (!$mediaParser = $mediaParser->getParser($request->get('video_url'))) {
            return new JsonResponse(array(
                'error' => true
            ));
        }

        /** @var Media $media */
        $media = $doctrineInit->initEntity(new Media());
        $media->setType('embedvideo');
        $media->setUrl($request->get('video_url'));
        $media->setName($request->get('video_url'));
        $media->setMimeType('text/html');
        $media->setSource($mediaParser->getSource());
        $media->setEmbedId($mediaParser->getId());
        $media->setSize(0);
        $media->setMediaPath($mediaParser->getEmbedUrl());

        $app = MediaController::getRefererApp($this->container);
        $media->setApp($app);

        $folderId = $request->request->get('folderId');
        if ('root' != $folderId) {
            if ($folder = $this->getDoctrine()->getRepository('MediaBundle:Folder')->find($folderId)) {
                $media->setFolder($folder);
            }
        }

        $this->updateThumbnail($media, $mediaParser);

        $this->getEm()->persist($media);
        $this->getEm()->flush();

        // Creating base image file

        $originalImagePath = $media->getThumbnail()->getWebPath('media');

        try {
            $this->get('media.filter_manager')->createBase($originalImagePath);
        } catch (\Exception $exception) {
            $error = $this->get('translator')->trans('flash.error.create_thumbnail', [], 'media_manager');
        }

        return new JsonResponse(array(
            "message" => $t->trans('File uploaded', [], 'media_manager')
        ));
    }

    /**
     * Edit embed video detail
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
                throw $this->createNotFoundException('Unanble to find the media');
            }

            if ($media->isLocked()) {
                return new JsonResponse(['error' => 'locked']);
            }

            $form = $this->createForm(EmbedVideoType::class, $media);

            if ("POST" == $request->getMethod()) {

                $oldUrl = $media->getUrl();
                $form->handleRequest($request);

                $mediaParser = $this->get('media.parser');

                if ($oldUrl != $media->getUrl() && !$mediaParser = $mediaParser->getParser($form->get('url')->getData())) {

                    $t = $this->get('translator');

                    $form->get('url')->addError(new FormError($t->trans('This video url is not valid/supported.', [], 'media_manager')));

                }

                if ($form->isValid()) {
                    $this->getEm()->persist($media);

                    if ($oldUrl != $media->getUrl()) {

                        $media->setMediaPath($mediaParser->getEmbedUrl());
                        $media->setSource($mediaParser->getSource());
                        $media->setEmbedId($mediaParser->getId());

                        $this->updateThumbnail($media, $mediaParser);
                    }

                    $this->getEm()->flush();

                    $this->get('system.router_invalidator')->invalidate();
                }
            }

            return new JsonResponse(array(
                'html' => $this->renderView('MediaBundle:Media/Manager/component:_embedvideo_edit.html.twig', array(
                    'form' => $form->createView(),
                    'media' => $media,
                    'video_url' => $media->getMediaPath()
                ))
            ));
        }

        return new JsonResponse();
    }

    /**
     * @param Media $video
     * @param MediaParserInterface $mediaParser
     * @param DoctrineInit $doctrineInit
     */
    public function updateThumbnail(Media $video, MediaParserInterface $mediaParser, DoctrineInit $doctrineInit)
    {
        //The file needs to be download from a remote server and stored temporary on the server to allow doctrine extension to handle it properly
        $tempFile = '/tmp/' . uniqid('EmbedVideoThumbnail-') . '.jpg';

        $thumbnailUrl = $mediaParser->getThumbnailUrl();

        if (null == $thumbnailUrl) {
            $thumbnailUrl = $this->container->get('kernel')->getRootDir().'/../web/bundles/media/images/video-icon.png';
        }

        file_put_contents($tempFile, file_get_contents($thumbnailUrl));

        $thumbnailFile = new MediaFile($tempFile);
        $thumbnailFile = $thumbnailFile->getUploadedFile();

        if ($video->getThumbnail()) {
            $this->getEm()->remove($video->getThumbnail());
        }

        //Generate the thumbnail
        $image = $doctrineInit->initEntity(new Media());

        $image->setType('image');
        $image->setMimeType('image/jpeg');
        $image->setHidden(true);
        $image->setName("Preview - " . $video->getName());
        $image->setMedia($thumbnailFile);

        $app = MediaController::getRefererApp($this->container);
        $image->setApp($app);

        $this->getEm()->persist($image);

        $video->setThumbnail($image);
    }
}
