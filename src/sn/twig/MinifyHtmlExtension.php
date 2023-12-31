<?php

declare(strict_types=1);

namespace sn\twig;

use Twig\Environment;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Twig\TwigFunction;
use voku\helper\HtmlMin;

class MinifyHtmlExtension extends AbstractExtension
{
    /**
     * @var array
     */
    private $options = [
        'is_safe'           => ['html'],
        'needs_environment' => true,
    ];

    /**
     * @var callable
     */
    private $callable;

    /**
     * @var HtmlMin
     */
    private $minifier;

    /**
     * @var bool
     */
    private $forceCompression = false;

    /**
     * MinifyHtmlExtension constructor.
     *
     * @param HtmlMin $htmlMin
     * @param bool    $forceCompression Default: false. Forces compression regardless of Twig's debug setting.
     */
    public function __construct(HtmlMin $htmlMin, bool $forceCompression = false)
    {
        $this->forceCompression = $forceCompression;
        $this->minifier = $htmlMin;
        $this->callable = [$this, 'compress'];
    }

    /**
     * @param Environment $twig
     * @param string      $html
     *
     * @return string
     */
    public function compress(Environment $twig, $html)
    {
        if ($this->isCompressionActive($twig)) {
            $html = $this->minifier->minify($html);
        }

        return $html;
    }

    /** @noinspection PhpMissingParentCallCommonInspection */

    /**
     * @return array
     */
    public function getFilters(): array
    {
        return [
            new TwigFilter('htmlcompress', $this->callable, $this->options),
        ];
    }

    /** @noinspection PhpMissingParentCallCommonInspection */
    public function getFunctions(): array
    {
        return [
            new TwigFunction('htmlcompress', $this->callable, $this->options),
        ];
    }

    /** @noinspection PhpMissingParentCallCommonInspection */
    public function getTokenParsers(): array
    {
        return [
            new MinifyHtmlTokenParser(),
        ];
    }

    /**
     * @param Environment $twig
     *
     * @return bool
     */
    public function isCompressionActive(Environment $twig): bool
    {
        return $this->forceCompression
               ||
               !$twig->isDebug();
    }
}
