<?php

namespace App\Extensions;

use App\Library\ApplicationCoreInterface;
use App\Services\ApplicationCore;
use Symfony\Component\HttpFoundation\Request,
    Symfony\Component\DependencyInjection\ContainerInterface,
    Symfony\Component\HttpFoundation\RequestStack;

use Twig_Extension_GlobalsInterface;


/**
 * Library of helper functions
 */
class TwigExtension extends \Twig_Extension implements Twig_Extension_GlobalsInterface
{
    /**
     * @var Request
     */
    private $request;

    /**
     * @var String
     */
    private $locale;

    /**
     * @var ApplicationCoreInterface
     */
    private $applicationCore;

    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * TwigExtension constructor.
     * @param ApplicationCore $applicationCore
     * @param ContainerInterface $container
     */
    public function __construct(ApplicationCore $applicationCore, ContainerInterface $container)
    {
        $this->applicationCore = $applicationCore;
        $this->container = $container;
    }

    /**
     * @param ApplicationCoreInterface $applicationCore
     */
    public function setApplicationCore(ApplicationCoreInterface $applicationCore)
    {
        $this->applicationCore = $applicationCore;
    }

    /**
     * Set the locale
     *
     * @param string $locale
     */
    public function setLocale($locale)
    {
        $this->locale = $locale;
    }

    /**
     * @param RequestStack $requestStack
     */
    public function setRequest(RequestStack $requestStack)
    {
        $this->request = $requestStack->getCurrentRequest();
    }

    /**
     * List of available functions
     *
     * @return array
     */
    public function getFunctions()
    {
        return [
            new \Twig_SimpleFunction('isExternalUrl', [$this, 'isExternalUrl']),
            new \Twig_SimpleFunction('dateRange', [$this, 'dateRange']),
            new \Twig_SimpleFunction('tree_indentation', [$this, 'treeIndentation'])
        ];
    }

    /**
     * List of available filters
     *
     * @return array
     */
    public function getFilters()
    {
        return [
            new \Twig_SimpleFilter('trim', [$this, 'trim']),
            new \Twig_SimpleFilter('stripLineBreaks', [$this, 'stripLineBreaks']),
            new \Twig_SimpleFilter('formatCurrency', [$this, 'formatCurrency']),
            new \Twig_SimpleFilter('ceil', [$this, 'ceil'])
        ];
    }

    public function getGlobals()
    {
        $section = null;

        if ($this->applicationCore->isReady()) {
            $section = $this->applicationCore->getSection();
        } else {
            $section = null;
        }

        return array(
            'section' => $section,
            'project_title' => $this->container->getParameter('app.metadata.title'),
            'project_description' => $this->container->getParameter('app.metadata.description'),
            'project_keywords' => $this->container->getParameter('app.metadata.keywords')
        );
    }

    /**
     * Determine if an url is external
     *
     * @param string $url
     *
     * @return bool
     */
    public function isExternalUrl($url)
    {
        $trustedHostPatterns = $this->request->getTrustedHosts();

        if (count($trustedHostPatterns) > 0) {
            $parse = parse_url($url);

            foreach ($trustedHostPatterns as $pattern) {
                if (preg_match($pattern, $parse['host'])) {
                    return false;
                }
            }
        }

        return true;
    }

    /**
     * Returns a textual representation of a date range
     *
     * @param \DateTime $startDate
     * @param \DateTime $endDate
     * @param string    $locale
     *
     * @return string
     */
    public function dateRange($startDate, $endDate, $locale = null)
    {
        if (!$locale) {
            $locale = $this->locale;
        }

        $defaultDateFormatter = \IntlDateFormatter::create($locale, \IntlDateFormatter::LONG, \IntlDateFormatter::NONE);

        if ($startDate == $endDate) {
            return $defaultDateFormatter->format($startDate);
        }

        $startDateInfos = date_parse($startDate->format('Y-m-d'));
        $endDateInfos = date_parse($endDate->format('Y-m-d'));

        if ($startDateInfos['month'] == $endDateInfos['month'] && $startDateInfos['year'] == $endDateInfos['year']) {

            // ex.: 2 au 5 février 2012
            if ($locale == 'fr') {
                $range = $startDateInfos['day'] . ' au ' . $defaultDateFormatter->format($endDate);

            // ex.: February 2 to 5, 2012
            } else {
                $dateFormatterStart = \IntlDateFormatter::create($locale, \IntlDateFormatter::LONG, \IntlDateFormatter::NONE, null, null, 'MMMM d');
                $dateFormatterEnd = \IntlDateFormatter::create($locale, \IntlDateFormatter::LONG, \IntlDateFormatter::NONE, null, null, 'd, Y');
                $range = $dateFormatterStart->format($startDate) . ' to ' . $dateFormatterEnd->format($endDate);
            }

        } elseif ($startDateInfos['year'] == $endDateInfos['year']) {

            // ex.: 2 février au 5 mai 2012
            if ($locale == 'fr') {
                $dateFormatterStart = \IntlDateFormatter::create($locale, \IntlDateFormatter::LONG, \IntlDateFormatter::NONE, null, null, 'd MMMM');
                $range = $dateFormatterStart->format($startDate) . ' au ' . $defaultDateFormatter->format($endDate);

            // ex.: February 2 to May 5, 2012
            } else {
                $dateFormatter = \IntlDateFormatter::create($locale, \IntlDateFormatter::LONG, \IntlDateFormatter::NONE, null, null, 'MMMM d');
                $range = $dateFormatter->format($startDate) . ' to ' . $dateFormatter->format($endDate) . ', ' . $endDateInfos['year'];
            }

        } else {
            $range = $defaultDateFormatter->format($startDate);
            $range .= $locale == 'fr' ? ' au ' : ' to ';
            $range .= $defaultDateFormatter->format($endDate);
        }

        if ($locale == 'fr') {
            $range = preg_replace(array('/^1 /', '/au 1 /'), array('1er ', 'au 1er '), $range);
        }

        return $range;
    }

    /**
     * Strip whitespace
     *
     * @param string $string
     *
     * @return string
     */
    public function trim($string)
    {
        return trim($string);
    }

    /**
     * Strip line breaks
     *
     * @param string $string
     *
     * @return string
     */
    public function stripLineBreaks($string)
    {
        return str_replace(array("\r\n", "\r", "\n"), "", $string);
    }

    /**
     * Round fractions up
     *
     * @param float $number
     *
     * @return integer
     */
    public function ceil($number)
    {
        return ceil($number);
    }

    /**
     * Format currency
     *
     * @param float  $price
     * @param string $locale
     * @param string $currency
     * @param bool   $showSymbol           Show or hide the dollard sign (works only for CAD and USD currencies for now)
     * @param bool   $showDecimalsWhenZero Show or hide decimals if they are equal to 0 (works only for CAD and USD currencies for now)
     *
     * @return string
     */
    public function formatCurrency($price, $locale = null, $currency = 'CAD', $showSymbol = true, $showDecimalsWhenZero = true)
    {
        if (!$locale) {
            $locale = $this->locale;
        }

        $format = new \NumberFormatter($locale, \NumberFormatter::CURRENCY);
        $formatedPrice = $format->formatCurrency($price, $currency);

        if ($currency == 'CAD' or $currency == 'USD') {

            $formatedPrice = str_replace(array('CA', 'US'), '', $formatedPrice);

            if (!$showSymbol) {
                $formatedPrice = str_replace('$', '', $formatedPrice);
            }

            if (!$showDecimalsWhenZero && $price - floor($price) == 0) {
                $formatedPrice = str_replace(array('.00', ',00'), '', $formatedPrice);
            }
        }

        return $formatedPrice;
    }

    /**
     * Name of this extension
     *
     * @return string
     */
    public function getName()
    {
        return 'system_extension';
    }

    /**
     * Tree Indentation
     *
     * Indent label or widget to render as a tree
     *
     * @param $level
     *
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
