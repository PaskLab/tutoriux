<?php

namespace App\Repository\School;

use App\Library\BaseEntityRepository,
    Tutoriux\DoctrineBehaviorsBundle\Model as TutoriuxORMBehaviors;

/**
 * Class RequestCriteriaRepository
 * @package App\Repository\School
 */
class RequestCriteriaRepository extends BaseEntityRepository
{
    use TutoriuxORMBehaviors\Repository\TranslatableEntityRepository;
}
