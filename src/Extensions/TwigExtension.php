<?php

namespace App\Extensions;

use Twig\Extension\GlobalsInterface;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;
use App\Services\ApplicationCore;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

/**
 * Class TwigExtension
 * @package SystemBundle\Extensions
 */
class TwigExtension extends AbstractExtension implements GlobalsInterface
{
    /**
     * @var ApplicationCore
     */
    private $applicationCore;

    /**
     * @var ParameterBagInterface
     */
    private $parameterBag;

    /**
     * TwigExtension constructor.
     * @param ApplicationCore $applicationCore
     * @param ParameterBagInterface $parameterBag
     */
    public function __construct(ApplicationCore $applicationCore, ParameterBagInterface $parameterBag)
    {
        $this->applicationCore = $applicationCore;
        $this->parameterBag = $parameterBag;
    }

    /**
     * @return array|TwigFunction[]
     */
    public function getFunctions()
    {
        return [
            new TwigFunction('tree_indentation', [$this, 'treeIndentation'])
        ];
    }

    /**
     * @return array
     */
    public function getGlobals()
    {
        $section = null;

        if ($this->applicationCore->isReady()) {
            $section = $this->applicationCore->getSection();
        } else {
            $section = null;
        }

        return [
            'section' => $section,
            'project_title' => $this->parameterBag->get('app.metadata.title'),
            'project_description' => $this->parameterBag->get('app.metadata.description'),
            'project_keywords' => $this->parameterBag->get('app.metadata.keywords')
        ];
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'tutoriux_extension';
    }

    /**
     * @param $level
     * @return string
     */
    public function treeIndentation($level)
    {
        $indent = '';

        for ($i = 2; $i <= $level; $i++) {
            $indent .= '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
        }

        return $indent;
    }
}
