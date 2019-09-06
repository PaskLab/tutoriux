<?php

namespace App\Controller\Globals\Media;

use Symfony\Component\HttpFoundation\Request,
    Symfony\Component\HttpFoundation\JsonResponse,
    Symfony\Component\HttpFoundation\File\UploadedFile,
    Symfony\Component\Validator\Constraints\File,
    Symfony\Component\Validator\ConstraintViolation;

use App\Library\BaseController,
    App\Library\Component\Media\MediaFile,
    App\Entity\Media\Media;
use App\Services\DoctrineInit;

/**
 * Class UploadController
 * @package App\Controller\Component\Media
 */
class UploadController extends BaseController
{
    /**
     * Upload
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function uploadAction(Request $request)
    {
        if ($request->isXmlHttpRequest()
            && $request->files->has('files')
            && $request->request->has('folderId')
            && $request->request->has('type')) {

            if ("POST" == $request->getMethod()) {

                $t = $this->get('translator');

                /** @var UploadedFile $file */
                $file = $request->files->get('files')[0];

                $jsonResponse = array(
                    "files" =>array(array(
                        "name" => $file->getClientOriginalName(),
                        "size" => $file->getSize()
                    ))
                );

                if ($file instanceof UploadedFile && $file->isValid()) {

                    // ALLOWED MIME TYPE && MAX UPLOAD SIZE

                    $allowedType = $this->container->getParameter('media.allowedMimeType');

                    switch ($request->request->get('type')) {
                        case 'image':
                            $allowedType = $allowedType['image'];
                            break;
                        case 'document':
                            $allowedType = $allowedType['document'];
                            break;
                        default:
                            $allowedType = array_merge($allowedType['image'], $allowedType['document'], $allowedType['video']);
                    }

                    $constraint = new File();
                    $constraint->maxSize = $this->container->getParameter('media.maxUploadSize') . 'M';
                    $constraint->maxSizeMessage = $t->trans('File size must be under %size% megabytes',
                        ['%size%' => $this->container->getParameter('media.maxUploadSize')], 'media_manager');
                    $constraint->mimeTypes = $allowedType;
                    $constraint->mimeTypesMessage = $t->trans('File type not allowed', [], 'media_manager');

                    $validator = $this->get('validator');
                    $violations = $validator->validate($file, $constraint);

                    if (count($violations)) {
                        $message = '';
                        /** @var ConstraintViolation $violation */
                        foreach ($violations as $violation) {
                            $message .= $violation->getMessage().". \n";
                        }

                        $jsonResponse['files'][0]['error'] = $message;
                        return new JsonResponse($jsonResponse);
                    }

                    switch ($file->getMimeType()) {
                        case 'image/jpeg':
                        case 'image/png':
                        case 'image/gif':
                            $uploadFunction = 'imageUpload';
                            break;
                        case 'application/pdf':
                            $uploadFunction = 'documentUpload';
                            break;
                        default:
                            $jsonResponse['files'][0]['error'] = $t->trans('Internal server error', [], 'media_manager');
                            return new JsonResponse($jsonResponse);
                    }

                    return $this->$uploadFunction($file);

                } else {

                    $jsonResponse['files'][0]['error'] = $t->trans('File type not allowed', [], 'media_manager');
                    return new JsonResponse($jsonResponse);
                }

            }

        }

        return new JsonResponse();
    }

    /**
     * @param UploadedFile $file
     * @param DoctrineInit $doctrineInit
     * @return JsonResponse
     */
    private function imageUpload(UploadedFile $file, DoctrineInit $doctrineInit)
    {
        /** @var Media $media */
        $media = $doctrineInit->initEntity(new Media());

        $media->setType('image');
        $media->setMedia($file);
        $media->setName($file->getClientOriginalName());

        $media->setMimeType($file->getClientMimeType());
        $media->setSize($file->getClientSize());

        $app = MediaController::getRefererApp($this->container);
        $media->setApp($app);

        $folderId = $this->get('request_stack')->getCurrentRequest()->request->get('folderId');
        if ('root' != $folderId) {
            if ($folder = $this->getDoctrine()->getRepository('MediaBundle:Folder')->find($folderId)) {
                $media->setFolder($folder);
            }
        }

        $this->getEm()->persist($media);

        $this->getEm()->flush();

        // Creating base image file to get the base image dimensions

        $cacheManager = $this->container->get('liip_imagine.cache.manager');

        $originalImagePath = $media->getWebPath('media');

        try {
            $this->get('media.filter_manager')->createBase($originalImagePath);
        } catch (\Exception $exception) {
            $error = $this->get('translator')->trans('flash.error.create_image', [], 'media_manager');
        }

        $baseImagePath = str_replace(
            $this->get('request_stack')->getCurrentRequest()->getSchemeAndHttpHost(),
            preg_replace('#(.*)/app#i', '$1/web', $this->get('kernel')->getRootDir()),
            $this->get('liip_imagine.cache.manager')->resolve($media->getWebPath('media'), 'base')
        );

        list($width, $height, $type, $attr) = getimagesize($baseImagePath);

        $media->setWidth($width);
        $media->setHeight($height);
        $this->getEm()->flush();

        return new JsonResponse(array('files' => array(array(
            'name' => $media->getName(),
            'size' => $media->getSize(),
            'url' => $media->getMediaPath(),
            'thumbnailUrl' => $cacheManager->getBrowserPath($media->getThumbnailUrl(), 'media_thumb'),
        ))));
    }

    /**
     *      * videoUpload
     *
     * Possible mimetype: video/mpeg, video/mp4, application/x-shockwave-flash, video/x-flv,
     *          video/quicktime, video/x-ms-wmv, video/x-msvideo
     *
     * @param UploadedFile $file
     * @param DoctrineInit $doctrineInit
     * @return JsonResponse
     */
    private function videoUpload(UploadedFile $file, DoctrineInit $doctrineInit)
    {
        $media = $doctrineInit->initEntity(new Media());

        $media->setType('video');
        $media->setContainer($this->container);
        $media->setMedia($file);
        $media->setName($file->getClientOriginalName());
        $media->setMimeType($file->getClientMimeType());
        $media->setSize($file->getClientSize());

        $app = MediaController::getRefererApp($this->container);
        $media->setApp($app);

        $folderId = $this->get('request_stack')->getCurrentRequest()->request->get('folderId');
        if ('root' != $folderId) {
            if ($folder = $this->getDoctrine()->getRepository('MediaBundle:Folder')->find($folderId)) {
                $media->setFolder($folder);
            }
        }

        $this->getEm()->persist($media);

        //Generate the thumbnail

        $thumbnailFile = new MediaFile($this->getVideoThumbnailPath($file));
        $thumbnailFile = $thumbnailFile->getUploadedFile();

        $image = $doctrineInit->initEntity(new Media());

        $image->setType('image');
        $image->setHidden(true);
        $image->setName("Preview - ".$file->getClientOriginalName());
        $image->setMedia($thumbnailFile);

        list($width, $height, $type, $attr) = getimagesize($thumbnailFile->getRealPath());

        $image->setWidth($width);
        $image->setHeight($height);
        $image->setMimeType($thumbnailFile->getClientMimeType());
        $image->setSize($thumbnailFile->getClientSize());

        $this->getEm()->persist($image);

        $media->setThumbnail($image);

        $this->getEm()->flush();

        $documentIcons = $this->container->getParameter('media.documentIcons');
        $cacheManager = $this->container->get('liip_imagine.cache.manager');

        return new JsonResponse(array('files' => array(array(
            'name' => $media->getName(),
            'size' => $file->getClientSize(),
            'url' => $media->getMediaPath(),
            'thumbnailUrl' => $cacheManager->getBrowserPath(
                    $documentIcons[$media->getMimeType()],
                    'document_thumb'
                )
        ))));
    }

    /**
     * @param UploadedFile $file
     * @param DoctrineInit $doctrineInit
     * @return JsonResponse
     */
    private function documentUpload(UploadedFile $file, DoctrineInit $doctrineInit)
    {
        $media = $doctrineInit->initEntity(new Media());

        $media->setType('document');
        $media->setContainer($this->container);
        $media->setMedia($file);
        $media->setName($file->getClientOriginalName());
        $media->setMimeType($file->getClientMimeType());
        $media->setSize($file->getClientSize());

        $app = MediaController::getRefererApp($this->container);
        $media->setApp($app);

        $folderId = $this->get('request_stack')->getCurrentRequest()->request->get('folderId');
        if ('root' != $folderId) {
            if ($folder = $this->getDoctrine()->getRepository('MediaBundle:Folder')->find($folderId)) {
                $media->setFolder($folder);
            }
        }

        $this->getEm()->persist($media);
        $this->getEm()->flush();

        $documentIcons = $this->container->getParameter('media.documentIcons');
        $cacheManager = $this->container->get('liip_imagine.cache.manager');

        return new JsonResponse(array('files' => array(array(
            'name' => $media->getName(),
            'size' => $file->getClientSize(),
            'url' => $media->getMediaPath(),
            'thumbnailUrl' => $cacheManager->getBrowserPath(
                    $documentIcons[$media->getMimeType()],
                    'document_thumb'
                )
        ))));
    }
}