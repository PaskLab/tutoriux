<?php

namespace App\Controller\Site\User;

use Symfony\Component\HttpFoundation\JsonResponse,
    Symfony\Component\HttpFoundation\Request,
    Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException,
    Symfony\Component\HttpKernel\Exception\NotFoundHttpException,
    Symfony\Component\HttpFoundation\Response;

use App\Library\BaseController,
    App\Entity\User;

/**
 * Class AuthorController
 * @package App\Controller\Site\User
 */
class AuthorController extends BaseController
{
    /**
     * @param Request $request
     * @return Response|MethodNotAllowedHttpException
     */
    public function authors(Request $request)
    {
        switch ($request->getMethod()) {
            case 'GET':
                return $this->authorsGET($request);
                break;
            default:
                return new MethodNotAllowedHttpException(['GET']);
        }
    }

    /**
     * @param Request $request
     * @return Response
     */
    public function authorsGET(Request $request)
    {
        if ($request->get('username')) {

            $user = $this->getRepository('SystemBundle:User')->createQueryBuilder('u')
                ->select('u')
                ->innerJoin('u.roles', 'ur')
                ->where('u.username = :username AND ur.role = :role AND u.active = TRUE')
                    ->setParameters([
                        'username' => $request->get('username'),
                        'role' => 'ROLE_AUTHOR'
                    ])
                ->setMaxResults(1)
                ->getQuery()->getOneOrNullResult();

            if (!$user) {
                throw new NotFoundHttpException('This author does not exist or has been disabled');
            }

            $this->createAndPushNavigationElement(
                (string) $user,
                'section_id_45',
                ['username' => $user->getUsername()],
                'fa fa-user'
            );

            return $this->render('site/user/author_profile.html.twig', [
                'user' => $user
            ]);
        }

        if ($request->isXmlHttpRequest()) {

            $data = $this->dataTableResult(
                $request->query->all(),
                $request->getLocale()
            );

            $formattedData = [];
            
            foreach ($data['data'] as $record) {
                /** @var User $user */
                $user = $record['user'];
                $formattedData[] = [
                    $this->getColumnContent($user, 0),
                    $record['document_count']
                ];
            }

            $data['data'] = $formattedData;

            return new JsonResponse($data);
        }

        return $this->render('site/user/authors.html.twig');
    }

    /**
     * @param User $entity
     * @param $columnIndex
     * @return string
     */
    private function getColumnContent(User $entity, $columnIndex)
    {
        return $this->renderView(
            'site/user/_authors_items.html.twig',
            ['entity' => $entity, 'column' => $columnIndex]
        );
    }

    /**
     * This render the modules that are associated with _profile_module navigation
     *
     * @return Response
     */
    public function profileModule()
    {
        $mappings = $this->getDoctrine()->getRepository('SystemBundle:Mapping')
            ->createQueryBuilder('m')
            ->select('m')
            ->innerJoin('m.navigation', 'n')
            ->where('n.code = :code')
            ->setParameter('code', '_profile_module')
            ->orderBy('m.ordering', 'ASC')
            ->getQuery()->getResult();

        return $this->render(
            'site/user/_profile_module.html.twig',
            [
                'mappings' => $mappings
            ]
        );
    }

    /**
     * @param array $parameters
     * @param $locale
     * @return array
     */
    private function dataTableResult(array $parameters, $locale)
    {
        $MAX_ALIAS_LENGTH = 4;

        $queryBuilderBase = $this->getEm()->getRepository('SystemBundle:User')
            ->createQueryBuilder('u');

        // Initial
        $queryBuilderBase->where('u.active = true')
            ->innerJoin('u.roles', 'ur')
            ->andWhere('ur.role = :role')
            ->setParameters([
                'role' => 'ROLE_AUTHOR'
            ])
            ->distinct(true);

        $queryBuilder = clone $queryBuilderBase;

        $queryBuilder
            ->select('u', $queryBuilder->expr()->count('dt') . 'AS document_count')
            ->leftJoin(
                $this->getEm()->getRepository('DocumentBundle:DocumentTranslation')->getClassName(),
                'dt', 'WITH', 'u.id = dt.createdBy'
            )
            ->andWhere('dt.published = TRUE')
            ->groupBy('u.id');

        $mapping = [
            0 => 'COALESCE(NULLIF(CONCAT(CONCAT(u.firstname, '
                .$queryBuilder->expr()->literal(' ').'), u.lastname), '
                .$queryBuilder->expr()->literal(' ').'), u.username)'
        ];

        // add alias for expressions
        foreach ($mapping as $key => $expr) {
            if (strpos($expr, '.') > $MAX_ALIAS_LENGTH) {
                $queryBuilder->addSelect($mapping[$key].' as alias_'.$key);
            }
        }

        // Search parameter
        if (isset($parameters['search']) && $parameters['search']['value'] != '') {

            $andWhere = '';

            foreach ($parameters['columns'] as $key => $column) {
                if ($column['searchable'] === 'true') {
                    $andWhere .= ((empty($andWhere)) ? '' : ' OR ')
                        . 'LOWER(' . $mapping[(int) $column['data']] . ') LIKE :search_value';
                }
            }

            $queryBuilder
                ->andWhere($andWhere)
                ->setParameter('search_value', '%'.mb_strtolower($parameters['search']['value'], 'UTF-8').'%');
        }

        // Order parameter
        if (isset($parameters['order'])) {
            foreach ($parameters['order'] as $column) {

                $field = $mapping[(int) $column['column']];

                if (strpos($field, '.') > $MAX_ALIAS_LENGTH) {
                    $field = 'alias_' . (int) $column['column'];
                }

                $queryBuilder->orderBy($field, $column['dir']);
            }
        } else {
            $queryBuilder->orderBy('alias_0', 'ASC');
        }

        // Limit and offset parameter
        $queryBuilder
            ->setFirstResult((int) $parameters['start'])
            ->setMaxResults((int) $parameters['length']);

        $results = $queryBuilder->getQuery()->getResult();

        // Insure standardization of results
        foreach ($results as $key => $result) {
            if (false == is_object($results)) {
                $results[$key]['user'] = $result[0];
                $results[$key]['document_count'] = $result['document_count'];
            }
        }

        // recordsTotal count
        $queryBuilderBase->select('COUNT(u)');
        $recordsTotal = $queryBuilderBase->getQuery()->getSingleScalarResult();

        return [
            'draw' => (int) $parameters['draw'],
            'recordsTotal' => $recordsTotal,
            'recordsFiltered' => count($results),
            'data' => $results
        ];
    }
}