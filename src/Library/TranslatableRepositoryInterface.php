<?php

namespace App\Library;

use Doctrine\ORM\QueryBuilder,
    Doctrine\Common\Persistence\ObjectRepository,
    Doctrine\Common\Collections\Selectable;

/**
 * Interface TranslatableRepositoryInterface
 *
 * Methods are defined in App\Library\BaseEntityRepository.php class
 * and Tutoriux\DoctrineBehaviorsBundle\Model\Repository\TranslatableEntityRepository.php trait
 *
 * @package App\Library
 */
interface TranslatableRepositoryInterface extends ObjectRepository, Selectable
{
    /**
     * Define if method processQuery should return a QueryBuilder instance or query result
     *
     * @param bool $returnQueryBuilder
     * @return $this
     */
    public function setReturnQueryBuilder(bool $returnQueryBuilder);

    /**
     * Define if method processQuery should return a QueryBuilder instance or query result
     *
     * @return bool
     */
    public function getReturnQueryBuilder(): bool;

    /**
     * @param string $locale
     * @return $this
     */
    public function setLocale(string $locale);

    /**
     * @return string
     */
    public function getLocale(): string;

    /**
     * @return bool
     */
    public function isEditMode(): bool;

    /**
     * @param bool $editMode
     * @return $this
     */
    public function setEditMode(bool $editMode);
}