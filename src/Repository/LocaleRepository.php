<?php

namespace App\Repository;

use App\Library\BaseEntityRepository;

/**
 * Class LocaleRepository
 * @package App\Repository
 */
class LocaleRepository extends BaseEntityRepository
{
    /**
     * Find All Except
     *
     * @param string $localeCode The locale Code we don't want to match
     *
     * @return array
     */
    public function findAllExcept($localeCode)
    {
        $query = $this->getEntityManager()->createQuery(
            'SELECT l FROM SystemBundle:Locale l
            WHERE l.code != :code
            AND l.active = :active
            ORDER BY l.ordering'
        )->setParameters(array(
            'code' => $localeCode,
            'active' => true,
        ));

        return $query->getResult();
    }
}
