<?php

namespace App\Repository\School;

use App\Library\BaseEntityRepository;
use Tutoriux\DoctrineBehaviorsBundle\Model as TutoriuxORMBehaviors;
use Tutoriux\DoctrineBehaviorsBundle\Model\Repository\TranslatableRepositoryInterface;

/**
 * Class RequestCriteriaRepository
 * @package App\Repository\School
 */
class RequestCriteriaRepository extends BaseEntityRepository implements TranslatableRepositoryInterface
{
    use TutoriuxORMBehaviors\Repository\TranslatableEntityRepository;
}
