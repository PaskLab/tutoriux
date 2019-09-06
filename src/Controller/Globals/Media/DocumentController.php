<?php

namespace App\Controller\Globals\Media;

use Symfony\Component\HttpFoundation\File\UploadedFile,
    Symfony\Component\HttpFoundation\JsonResponse,
    Symfony\Component\HttpFoundation\Request;

use App\Library\BaseController,
    App\Entity\Media\Media,
    App\Form\Globals\Media\DocumentType;

/**
 * Class DocumentController
 * @package App\Controller\Component\Media
 */
class DocumentController extends BaseController
{
    /**
     * Edit document detail
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

            $form = $this->createForm(DocumentType::class, $media);

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

            return new JsonResponse([
                'html' => $this->renderView('MediaBundle:Media/Manager/component:_document_edit.html.twig', [
                    'form' => $form->createView(),
                    'media' => $media,
                    'fileExtension' => MediaController::guessExtension($media->getMediaPath()),
                    'realName' => $realName
                ])
            ]);
        }

        return new JsonResponse();
    }

    /**
     * Get Thumbnail path
     *
     * @param UploadedFile $file
     * @return string
     */
    private function getThumbnailPath(UploadedFile $file)
    {
        switch ($file->getMimeType()) {
            case 'application/pdf':
                return $this->createPdfPreview($file->getPathname());
            case 'application/msword':
                return $this->container->get('kernel')->getRootDir().'/../web/bundles/media/images/word-icon.png';
            case 'application/vnd.openxmlformats-officedocument.wordprocessingml.document':
                return $this->container->get('kernel')->getRootDir().'/../web/bundles/media/images/word-icon.png';
            case 'application/vnd.oasis.opendocument.text':
                return $this->container->get('kernel')->getRootDir().'/../web/bundles/media/images/writer-icon.jpg';
            default:
                return $this->container->get('kernel')->getRootDir().'/../web/bundles/media/images/file-icon.png';
        }
    }

    /**
     * Generate a pdf preview if "convert" is present on the host system
     *
     * @param $path
     * @return string
     */
    private function createPdfPreview($path)
    {
        if (shell_exec("which convert")) {
            $target = $path.'.jpg';
            $command = sprintf("convert %s[0] %s", $path, $target);
            if (!shell_exec($command)) {
                return $target;
            }
        }

        return $this->container->get('kernel')->getRootDir().'/../web/bundles/media/images/pdf-icon.png';
    }
}
