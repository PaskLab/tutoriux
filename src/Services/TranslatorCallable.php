<?php

namespace App\Services;

use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Class TranslatorCallable
 * @package App\Services
 */
class TranslatorCallable
{
    /**
     * @var TranslatorInterface
     */
    private $translator;

    /**
     * TranslatorCallable constructor.
     * @param TranslatorInterface $translator
     */
    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    /**
     * @return TranslatorInterface
     */
    public function __invoke()
    {
        return $this->translator;
    }
}