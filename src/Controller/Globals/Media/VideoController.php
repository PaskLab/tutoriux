<?php

namespace App\Controller\Globals\Media;

use Symfony\Component\HttpFoundation\JsonResponse,
    Symfony\Component\HttpFoundation\Request;

use App\Form\Globals\Media\VideoType,
    App\Library\BaseController;

/**
 * Class VideoController
 * @package App\Controller\Component\Media
 */
class VideoController extends BaseController
{
    /**
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

            $form = $this->createForm(VideoType::class, $media);

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
                'html' => $this->renderView('MediaBundle:Media/Manager/component:_video_edit.html.twig', array(
                    'form' => $form->createView(),
                    'media' => $media,
                    'fileExtension' => MediaController::guessExtension($media->getMediaPath()),
                    'realName' => $realName
                ))
            ));
        }

        return new JsonResponse();
    }
}
