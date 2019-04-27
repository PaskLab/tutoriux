<?php

namespace App\Repository\School;

use App\Library\BaseEntityRepository;
use App\Library\TranslatableRepositoryInterface;
use Tutoriux\DoctrineBehaviorsBundle\Model as TutoriuxORMBehaviors;

/**
 * Class RequestCriteriaRepository
 * @package App\Repository\School
 */
class RequestCriteriaRepository extends BaseEntityRepository implements TranslatableRepositoryInterface
{
    use TutoriuxORMBehaviors\Repository\TranslatableEntityRepository;
}
