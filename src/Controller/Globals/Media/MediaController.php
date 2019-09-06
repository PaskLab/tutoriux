<?php

namespace App\Controller\Globals\Media;

use Symfony\Component\HttpFoundation\JsonResponse,
    Symfony\Component\HttpFoundation\Request,
    Symfony\Component\DependencyInjection\ContainerInterface;

use Doctrine\ORM\Mapping\ClassMetadata;

use App\Entity\Media\Media;
use App\Entity\Media\Folder;
use App\Library\Component\Media\MediaPager;
use App\Library\BaseController;
use App\Services\Deletable;

use App\Services\DoctrineInit;
use Wa72\HtmlPageDom\HtmlPageCrawler;

/**
 * Class MediaController
 * @package App\Controller\Component\Media
 */
class MediaController extends BaseController
{
    /**
     * Load Media
     *
     * @param Request $request
     * @return JsonResponse
     * @throws \Exception
     */
    public function loadAction(Request $request)
    {
        if ($request->isXmlHttpRequest()
            && $request->query->has('type')
            && $request->query->has('text')
            && $request->query->has('sort')
            && $request->query->has('folderId')
            && $request->query->has('view')) {

            $app = $this::getRefererApp($this->container);

            $this->getDoctrine()->getRepository('MediaBundle:Media')->setReturnQueryBuilder(true);

            $mediaQb = $this->getDoctrine()->getRepository('MediaBundle:Media')->findByFolderType(
                $request->query->get('folderId', 'root'),
                $request->query->get('type', 'any'),
                $request->query->get('view'),
                $request->query->get('sort', 'newer'),
                $request->query->get('text', ''),
                $app,
                $this->getUser()
            );

            $tree = [];

            if ($request->query->get('init', false)) {

                $rootFolders = $this->getDoctrine()->getRepository('MediaBundle:Folder')
                    ->findFoldersTree($app, $this->getUser());

                /* @var $folder Folder */
                foreach ($rootFolders as $folder) {
                    $tree[] = $folder->toArray();
                }
            }

            $mediaPager = new MediaPager(
                $mediaQb,
                1,
                $this->container->getParameter('media.resultPerPage')
            );

            $template = ('true' == $request->query->get('init', 'false')) ? 'MediaBundle:Media/Manager:manager.html.twig'
                : 'MediaBundle:Media/Manager/component:_list.html.twig';

            return new JsonResponse(array(
                'html' => $this->renderView($template, array(
                        'medias' => $mediaPager->getResult(),
                        'folderId' => $request->query->get('folderId', 'root'),
                        'type' => $request->query->get('type', 'image'),
                        'text' => $request->query->get('text', ''),
                        'sort' => $request->query->get('sort', 'newer'),
                        'view' => $request->query->get('view')
                 )),
                'tree' => $tree
            ));
        }

        return new JsonResponse(array());
    }

    /**
     * Move folder or media
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function moveAction(Request $request)
    {
        if ($request->isXmlHttpRequest()
            && $request->query->has('type')
            && $request->query->has('elementId')
            && $request->query->has('targetId')) {

            if ('folder' == $request->query->get('type')) {
                $folderSource = $this->getDoctrine()->getRepository('MediaBundle:Folder')
                    ->find($request->query->get('elementId'));

                if ('root' == $request->query->get('targetId')) {
                    $folderSource->setParent();
                } else {
                    $folderTarget = $this->getDoctrine()->getRepository('MediaBundle:Folder')
                        ->find($request->query->get('targetId'));
                    $folderSource->setParent($folderTarget);
                }
            } else {
                $media = $this->getDoctrine()->getRepository('MediaBundle:Media')
                    ->find($request->query->get('elementId'));

                if ('root' == $request->query->get('targetId')) {
                    $media->setFolder();
                } else {
                    $folderTarget = $this->getDoctrine()->getRepository('MediaBundle:Folder')
                        ->find($request->query->get('targetId'));
                    $media->setFolder($folderTarget);
                }
            }

            $this->getDoctrine()->getManager()->flush();
        }

        return new JsonResponse();
    }

    /**
     * Create New Folder
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function createFolderAction(Request $request)
    {
        if ($request->isXmlHttpRequest() && $request->query->has('parentFolderId')) {

            $newFolder = new Folder();
            $newFolder->setName($this->get('translator')->trans('New Folder', [], 'media_manager'));

            if ('root' != $request->query->get('parentFolderId')) {

                $parentFolder = $this->getDoctrine()->getRepository('MediaBundle:Folder')
                    ->find($request->query->get('parentFolderId'));

                if ($parentFolder) {
                    $newFolder->setParent($parentFolder);
                }
            }

            $app = $this::getRefererApp($this->container);
            $newFolder->setApp($app);

            $this->getEm()->persist($newFolder);
            $this->getEm()->flush();

            return new JsonResponse(array('id' => $newFolder->getId(), 'text' => $this->get('translator')->trans('New Folder', [], 'media_manager')));
        }

        return new JsonResponse();
    }

    /**
     * @param Request $request
     * @param Deletable $deletableService
     * @return JsonResponse
     */
    public function deleteFolderAction(Request $request, Deletable $deletableService)
    {
        if ($request->isXmlHttpRequest() && $request->query->has('folderId')) {

            $folder = $this->getDoctrine()->getRepository('MediaBundle:Folder')
                ->find($request->query->get('folderId'));

            if ($folder) {

                $deletable = $deletableService->checkDeletable($folder);

                if ($deletable->isSuccess()) {
                    $this->getEm()->remove($folder);
                    $this->getEm()->flush();
                }

                return new JsonResponse(array(
                    'removed' => $deletable->isSuccess(),
                    'message' => (is_array($deletable->getErrors()->toArray())) ? implode('<br>', $deletable->getErrors()->toArray()) : null
                ));
            }
        }

        return new JsonResponse();
    }

    /**
     * Rename a folder
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function renameFolderAction(Request $request)
    {
        if ($request->isXmlHttpRequest()
            && $request->query->has('folderId')
            && $request->query->has('folderTitle')) {

            $t = $this->get('translator');

            if ('' == $request->query->get('folderTitle')) {

                return new JsonResponse(array(
                    'renamed' => false,
                    'message' => $t->trans('You must enter a name.', [], 'media_manager')
                ));

            } else {
                $folder = $this->getDoctrine()->getRepository('MediaBundle:Folder')
                    ->find($request->query->get('folderId'));

                if ($folder) {

                    $folder->setName($request->query->get('folderTitle'));

                    $this->getEm()->flush();

                    return new JsonResponse(array(
                        'renamed' => true
                    ));
                }
            }
        }

        return new JsonResponse();
    }

    /**
     * Return all related contents
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function relatedContentAction(Request $request)
    {
        if ($request->isXmlHttpRequest() && $request->query->has('mediaId')) {

            $media = $this->getDoctrine()->getRepository('MediaBundle:Media')
                ->find($request->query->get('mediaId'));

            if ($media) {
                $relatedContents = $this::getRelatedContents($media, $this->container);

                $relatedContents = array_merge($relatedContents['text'], $relatedContents['field']);

                $readableRelatedContents = $this->getReadableRelatedContents($relatedContents);

                $explode = explode('/', $media->getMediaPath());
                $realName = array_pop($explode);

                return new JsonResponse(array(
                    'html' => $this->renderView('MediaBundle:Media/Manager/component:_related_content.html.twig', array(
                        'media' => $media,
                        'relatedContents' => $readableRelatedContents,
                        'fileExtension' => MediaController::guessExtension($media->getMediaPath()),
                        'realName' => $realName
                    ))
                ));
            }
        }

        return new JsonResponse();
    }

    /**
     * Replace media associations
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function relatedContentReplaceAction(Request $request)
    {
        if ($request->isXmlHttpRequest()
            && $request->query->has('hashes')
            && $request->query->has('mediaId')
            && $this->get('security.authorization_checker')->isGranted('ROLE_CONTENT_MANAGER')) {

            $metadataFactory = $this->getEm()->getMetadataFactory();

            $media = $this->getDoctrine()->getRepository('MediaBundle:Media')
                ->find($request->query->get('mediaId'));
            $replacement = $this->getDoctrine()->getRepository('MediaBundle:Media')
                ->find($request->query->get('mediaReplacementId', 0));

            if ($media) {

                if ($media->isLocked()) {
                    return new JsonResponse(['error' => 'locked']);
                }

                $relatedContent = $this->getRelatedContents($media, $this->container);
                $relatedContent = $this->getReadableRelatedContents(array_merge($relatedContent['field'], $relatedContent['text']));

                foreach ($relatedContent as $entityType) {
                    foreach ($entityType['children'] as $field) {
                        foreach ($field['children'] as $content) {
                            if (in_array($content['hash'], $request->query->get('hashes'))) {

                                $repository = $this->getEm()->getRepository($content['class']);

                                if ($repository && $entity = $repository->find($content['id'])) {

                                    $metadata = $metadataFactory->getMetadataFor($content['class']);
                                    $fieldType = $metadata->getTypeOfField($content['field']);
                                    if ($fieldType == 'text') {
                                        $this->replaceMediaFromTexts($media, array(array($content['field'] => array($entity))), $replacement);
                                    } else {
                                        $this->replaceMediaRelation(array(array($content['field'] => array($entity))), $replacement);
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }

        return new JsonResponse();
    }

    /**
     * @param Request $request
     * @param Deletable $deletableService
     * @return JsonResponse
     * @throws \Doctrine\ORM\Mapping\MappingException
     */
    public function deleteMediaAction(Request $request, Deletable $deletableService)
    {
        $lockedMedias = [];

        if ($request->isXmlHttpRequest() && $request->query->has('mediaIds')) {

            $medias = [];

            foreach ($request->query->get('mediaIds') as $id) {

                /** @var Media $media */
                $media = $this->getDoctrine()->getRepository('MediaBundle:Media')->find($id);

                if ($media) {

                    if ($media->isLocked()) {
                        $lockedMedias[] = $media->getId();
                    } else {

                        $relatedContents = $this::getRelatedContents($media, $this->container);

                        if ($request->query->has('delete')) {

                            // Unlink content in case 'onDelete set null' hasn't been set
                            $this->replaceMediaRelation($relatedContents['field']);

                            // Remove the file from all texts where it is used
                            $this->replaceMediaFromTexts($media, $relatedContents['text']);

                            if ('embedvideo' == $media->getType()) {
                                $thumbnail = $media->getThumbnail();

                                if ($deletableService->checkDeletable($thumbnail)->isSuccess() && $deletableService->checkDeletable($media)->isSuccess()) {

                                    $this->get('liip_imagine.cache.manager')->remove(array(
                                        $media->getWebPath('media'),
                                        $thumbnail->getWebPath('media')
                                    ));

                                    if ('embedvideo' == $media->getType()) {
                                        $media->setMediaPath(null);
                                        $this->getEm()->flush();
                                    }

                                    $this->getEm()->remove($thumbnail);
                                    $this->getEm()->remove($media);
                                    $this->getEm()->flush();
                                }
                            } elseif ($deletableService->checkDeletable($media)->isSuccess()) {
                                $this->get('liip_imagine.cache.manager')->remove($media->getWebPath('media'));
                                $this->getEm()->remove($media);
                                $this->getEm()->flush();
                            }

                        } else {

                            $medias[$media->getId()] = [
                                'name' => $media->getName(),
                                'relatedContents' => $this->getReadableRelatedContents(array_merge($relatedContents['field'], $relatedContents['text']))
                            ];
                        }
                    }
                }

            }

            if (!$request->query->has('delete') && count($medias)) {
                return new JsonResponse([
                    'message' => $this->renderView('MediaBundle:Core:delete_message.html.twig', [
                        'medias' => $medias
                    ])
                ]);
            }
        }

        return new JsonResponse([
            'locked' => $lockedMedias
        ]);
    }

    /**
     * Guess extension of a file via his file path
     *
     * @param $filePath
     * @return mixed
     */
    public static function guessExtension($filePath)
    {
        $explode = explode('.', $filePath);
        return array_pop($explode);
    }

    /**
     * Duplicate a media
     *
     * @param Request $request
     * @param DoctrineInit $doctrineInit
     * @return JsonResponse
     */
    public function duplicateAction(Request $request, DoctrineInit $doctrineInit)
    {
        if ($request->isXmlHttpRequest() && $request->query->has('mediaIds')) {

            /** @var Media $media */
            $media = $this->getDoctrine()->getRepository('MediaBundle:Media')
                ->find($request->query->get('mediaIds')[0]);

            if ($media) {
                $newMedia = $doctrineInit->initEntity(clone($media));
                $newMedia->setName($media->getName() . ' - copy');

                $date = new \DateTime();

                $newMedia->setCreatedAt($date);
                $newMedia->setUpdatedAt($date);

                $thumbnailFile = $media->getThumbnail()->getMediaPath(true);

                $explodePath = explode('/', $thumbnailFile);
                $filename = $explodePath[count($explodePath) - 1];

                array_pop($explodePath);

                $path = implode('/', $explodePath) . '/';

                $increment = 0;

                do {
                    $increment++;
                    $newThumbnailFileName = 'copy' . $increment . '-' . $filename;
                    $newThumbnailFile = $path . $newThumbnailFileName;
                } while (file_exists($newThumbnailFile));

                copy($thumbnailFile, $newThumbnailFile);

                $mediaPath = $media->getUploadableFields()['media'] . '/';

                if ('embedvideo' != $media->getType()) {

                    $newMedia->setMediaPath($mediaPath.$newThumbnailFileName);

                } else {

                    $newThumbnail = $doctrineInit->initEntity(clone $media->getThumbnail());
                    $newThumbnail->setMediaPath($mediaPath.$newThumbnailFileName);

                    $this->getEm()->persist($newThumbnail);
                    $newMedia->setThumbnail($newThumbnail);

                }

                $this->getEm()->persist($newMedia);
                $this->getEm()->flush();

                if (in_array($newMedia->getType(), ['image', 'embedvideo'])) {
                    try {
                        $this->get('media.filter_manager')->createBase($newMedia->getThumbnail()->getWebPath('media'));
                    } catch (\Exception $exception) {
                        $error = $this->get('translator')->trans('flash.error.duplicate_image', [], 'media_manager');
                    }
                }
            }
        }

        return new JsonResponse();
    }

    /**
     * Return all entities associated to the given media
     *
     * @param Media $media
     * @param ContainerInterface $container
     * @return array
     * @throws \Doctrine\ORM\Mapping\MappingException
     */
    public static function getRelatedContents(Media $media, ContainerInterface $container)
    {
        $em = $container->get('doctrine')->getManager();

        $metadataFactory = $em->getMetadataFactory();

        $metadata = $metadataFactory->getAllMetadata();

        $entitiesAssociated = array();
        $entitiesAssociated['field'] = array();
        $entitiesAssociated['text'] = array();

        /* @var $classMetadata ClassMetadata */
        foreach ($metadata as $classMetadata) {
            if ('MediaBundle\Entity\Media' != $classMetadata->getName()) {
                foreach ($classMetadata->getAssociationMappings() as $association) {

                    if ('MediaBundle\Entity\Media' == $association['targetEntity'] && $association['isOwningSide']) {
                        $fieldName = $association['fieldName'];
                        $sourceEntity = $association['sourceEntity'];

                        $explode = explode('\\', $sourceEntity);
                        $entityName = array_pop($explode);

                        $entities = $em->getRepository($sourceEntity)->findBy(array(
                            $fieldName => $media->getId()
                        ));

                        if ($entities) {
                            $entitiesAssociated['field'][$entityName][$fieldName] = $entities;
                        }
                    }
                }

                foreach ($classMetadata->getFieldNames() as $fieldName) {

                    $explode = explode('\\', $classMetadata->getName());
                    $entityName = array_pop($explode);

                    $fieldMapping = $classMetadata->getFieldMapping($fieldName);

                    if ('text' == $fieldMapping['type']) {
                        $entities = $em->getRepository($classMetadata->getName())->createQueryBuilder('t')
                            ->where('t.' . $fieldName . ' LIKE :expression')
                            ->orWhere('t.' . $fieldName . ' LIKE :escapedExpression')
                            ->setParameters([
                                'expression' => '%data-mediaid="'.$media->getId().'"%',
                                'escapedExpression' => '%data-mediaid=\\\"'.$media->getId().'\\\"%'
                            ])
                            ->getQuery()->getResult();

                        if ($entities) {
                            $entitiesAssociated['text'][$entityName][$fieldName] = $entities;
                        }
                    }
                }
            }
        }

        return $entitiesAssociated;
    }

    /**
     * @param array $relatedContent
     * @return array
     */
    public function getReadableRelatedContents(array $relatedContent)
    {
        $t = $this->get('translator');
        $readableRelatedContent = [];

        foreach ($relatedContent as $entity => $fields) {

            $entityNodes = array(
                'name' => $t->trans($entity, array(), 'related_content'),
                'children' => array()
            );

            foreach ($fields as $field => $contents) {

                $fieldNodes = array(
                    'name' => $t->trans($field, array(), 'related_content'),
                    'children' => array()
                );

                foreach ($contents as $content) {

                    if (method_exists($content, 'getTranslatable')) {
                        $targetEntity = $content->getTranslatable();
                        $targetEntity->setCurrentLocale($content->getLocale());
                    } else {
                        $targetEntity = $content;
                    }

                    $targetEntity2str = (method_exists($targetEntity, '__toString')) ? (trim(html_entity_decode(str_replace(["\r\n","\r"], ' ', substr(strip_tags($targetEntity->__toString()), 0, 100))))) : false;
                    $targetEntityRoute = (method_exists($targetEntity, 'getRouteBackend'))
                        ? $this->generateUrl($targetEntity->getRouteBackend(), $targetEntity->getRouteBackendParams(
                                array('sectionId' => $this->guessSection($targetEntity)['id']))
                        )
                        : false;

                    $contentNode = array(
                        'name' => ($targetEntity2str) ?: ' ( '. $entity . ' ) ',
                        'href' => ($targetEntityRoute) ?: null,
                        'hash' => md5(get_class($content).$field.$content->getId()),
                        'class' => get_class($content),
                        'field' => $field,
                        'id' => $content->getId(),
                        'section' => $this->guessSection($targetEntity)['name']
                    );

                    $fieldNodes['children'][] = $contentNode;

                }

                $entityNodes['children'][] = $fieldNodes;
            }

            $readableRelatedContent[] = $entityNodes;
        }

        return $readableRelatedContent;
    }

    /**
     * Replace related relations with $replacement
     *
     * @param array $associatedField
     * @param $replacement Media
     */
    private function replaceMediaRelation(array $associatedField, Media $replacement = null) {
        if (count($associatedField)) {
            foreach ($associatedField as $methodGroup) {
                foreach ($methodGroup as $methodName => $entities) {
                    foreach ($entities as $entity) {
                        $method = 'set' . ucfirst($methodName);
                        $entity->$method($replacement);
                    }
                }
            }
        }

        $this->getEm()->flush();
    }

    /**
     * Replace $media from any text containing it with $replacement
     *
     * @param Media $media
     * @param Media $replacement
     * @param array $associatedText
     */
    private function replaceMediaFromTexts(Media $media, array $associatedText, Media $replacement = null) {

        foreach ($associatedText as $entityGroup) {
            foreach ($entityGroup as $fieldName => $entities) {

                $getMethod = 'get' . ucfirst($fieldName);
                $setMethod = 'set' . ucfirst($fieldName);

                foreach ($entities as $entity) {

                    $crawler = new HtmlPageCrawler($entity->$getMethod());

                    if ($replacement) {
                        $crawler->filter('[data-mediaid="' . $media->getId() . '"]')->replaceWith($replacement->getHtmlTag());
                    } else {
                        $crawler->filter('[data-mediaid="' . $media->getId() . '"]')->remove();
                    }

                    $entity->$setMethod($crawler->saveHTML());
                }
            }
        }

        $this->getEm()->flush();
    }

    /**
     * modalSearch
     *
     * @param Request $request
     * @return JsonResponse
     * @throws \Exception
     */
    public function modalSearchAction(Request $request)
    {
        $results = ['items' => []];

        if ($request->isXmlHttpRequest()
            && $request->query->has('search')
            && $request->query->has('mediaId')
            && $request->query->has('view')) {

            $media = $this->getRepository('MediaBundle:Media')->find($request->query->get('mediaId'));

            $medias = $this->getDoctrine()->getRepository('MediaBundle:Media')->findByFolderType(
                null,
                $media->getType(),
                $request->query->get('view'),
                'alpha',
                $request->query->get('search'),
                $this->getCore()->getApp(),
                $this->getUser()
            );

            $mediaGroup = array();

            /** @var $media Media */
            foreach ($medias as $media) {
                $mediaGroup[$media->getType()][] = array(
                    'id' => $media->getId(),
                    'text' => $media->getName()
                );
            }

            $formattedResult = array();

            foreach ($mediaGroup as $groupName => $medias) {
                $formattedResult[] = array(
                    'text' => ucfirst($groupName) . ((count($medias) > 1) ? 's' : ''),
                    'children' => $medias
                );
            }

            $results['items'] = $formattedResult;
        }

        return new JsonResponse($results);
    }

    /**
     * Update html in  all content for a specific media
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function updateContentAction(Request $request)
    {
        if ($request->isXmlHttpRequest()
            && $request->query->has('mediaId')) {

            $metadataFactory = $this->getEm()->getMetadataFactory();

            $media = $this->getDoctrine()->getRepository('MediaBundle:Media')
                ->find($request->query->get('mediaId'));

            if ($media) {

                $relatedContent = $this->getRelatedContents($media, $this->container);
                $relatedContent = $this->getReadableRelatedContents(array_merge($relatedContent['field'], $relatedContent['text']));

                foreach ($relatedContent as $entityType) {
                    foreach ($entityType['children'] as $field) {
                        foreach ($field['children'] as $content) {

                            $repository = $this->getEm()->getRepository($content['class']);

                            if ($repository && $entity = $repository->find($content['id'])) {

                                $metadata = $metadataFactory->getMetadataFor($content['class']);
                                $fieldType = $metadata->getTypeOfField($content['field']);

                                if ($fieldType == 'text') {
                                    $this->replaceMediaFromTexts($media, array(array($content['field'] => array($entity))), $media);
                                }
                            }
                        }
                    }
                }
            }
        }

        return new JsonResponse();
    }

    /**
     * Guess Section
     *
     * @param $entity
     * @return array
     */
    private function guessSection($entity)
    {
        $section = [
            'id' => 0,
            'name' => null
        ];

        if (method_exists($entity, 'getSection')) {
            if ($entity->getSection()) {
                $section['id'] = $entity->getSection()->getId();
                $section['name'] = $entity->getSection()->getName();
            }
        }

        return $section;
    }

    /**
     * @param ContainerInterface $container
     * @return \SystemBundle\Entity\App
     */
    public static function getRefererApp(ContainerInterface $container) {

        $request = $container->get('request_stack')->getCurrentRequest();

        $referer = preg_replace(
            '#' . $request->getSchemeAndHttpHost() . '(/app_dev.php/|/)?(.*)#i',
            '$2',
            $request->headers->get('referer')
        );

        $apps = $container->get('doctrine')->getRepository('SystemBundle:App')->findAll();

        foreach ($apps as $app) {
            if ($app->getPrefix() && preg_match('#^'.$app->getPrefix().'#i', $referer)) {
                return $app;
            }
        }

        $app = $container->get('doctrine')->getRepository('SystemBundle:App')
            ->find(AppRepository::FRONTEND_APP_ID);

        return $app;
    }
}