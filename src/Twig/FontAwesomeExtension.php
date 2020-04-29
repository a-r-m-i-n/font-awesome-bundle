<?php declare(strict_types=1);
namespace Armin\FontAwesomeBundle\Twig;

use Twig\Extension\AbstractExtension;

/**
 * FontAwesome Twig Extension
 *
 * Usage examples:
 *
 * {{ fa("smile-beam") }} == {{ fa("fas smile-beam") }}
 * {{ fa("far smile-beam") }}
 * {{ fa("far smile-beam", {size: 256, color: '#d50', class: 'card shadow'}) }}
 * *
 */
class FontAwesomeExtension extends AbstractExtension
{
    private CONST DEFAULT_CLASS_NAME = 'fa-svg-icon';

    /**
     * @var \Symfony\Component\HttpKernel\KernelInterface
     */
    private $kernel;

    static protected $cache = [];

    public function __construct(\Symfony\Component\HttpKernel\KernelInterface $kernel)
    {
        $this->kernel = $kernel;
    }

    public function getFunctions()
    {
        return [
            new \Twig\TwigFunction('fa', [$this, 'fontAwesome'], ['is_safe' => ['html']])
        ];
    }

    public function getName() {
        return 'font_awesome_extension';
    }

    public function fontAwesome(string $icon, array $options = [])
    {
        // provide default options
        $options = array_merge([
            'size' => '',
            'color' => '',
            'class' => '',
        ], $options);

        list($type, $icon) = $this->extractTypeAndIcon($icon);

        $path = $this->buildIconPathAndCheckForExistance($type, $icon);
        if (!file_exists($path)) {
            throw new \RuntimeException('Given Font Awesome Icon "' . $icon . '" not found!');
        }

        // Prepare options
        $size = (string) $options['size'];
        $color = (string) $options['color'];
        $class = self::DEFAULT_CLASS_NAME;
        if (!empty($options['class'])) {
            $class .= ' ' . $options['class'];
        }

        // If the same icon is requested a second time, use a reference to symbol instead
        if (array_key_exists($path, self::$cache)) {
            $id = self::$cache[$path];
            return self::buildSvgSymbolReference($id, $size, $class, $color);
        }

        // Create and cache symbol
        return self::createSvgSymbol($type, $icon, $path, $size, $class, $color);
    }

    /**
     * @param string $icon
     * @return array [$type, $icon]
     */
    private function extractTypeAndIcon(string $icon): array
    {
        $iconParts = explode(' ', $icon);
        $type = 'solid';
        if (count($iconParts) === 2) {
            $firstPart = reset($iconParts);
            if ($firstPart === 'far') {
                $type = 'regular';
            } elseif ($firstPart === 'fab') {
                $type = 'brands';
            } elseif ($firstPart === 'fas') {
                $type = 'solid';
            } else {
                throw new \InvalidArgumentException(
                    'Font Awesome icon prefix "' . $firstPart . '" not allowed! Allowed are: "far", "fas" or "fab"'
                );
            }
            $icon = end($iconParts);
        } elseif (count($iconParts) === 1) {
            $icon = reset($iconParts);
        } else {
            throw new \InvalidArgumentException(
                'Invalid icon name given. Valid name would be: "fas fa-file" or "fa-file" or just "file"'
            );
        }

        if (strpos($icon, 'fa-') === 0) {
            $icon = substr($icon, 3);
        }
        return [$type, $icon];
    }

    private function buildIconPathAndCheckForExistance(string $type, string $icon): string
    {
        $path = $this->kernel->getProjectDir() . '/vendor/fortawesome/font-awesome/svgs/';
        $path .= $type . '/';
        $path .= $icon . '.svg';
        return $path;
    }

    private static function buildSvgSymbolReference(string $id, string $size, string $class, string $color): string
    {
        $style = '';
        if (!empty($size)) {
            $style .= 'width:' . $size . 'px;height:' . $size . 'px;';
            $size = ' width="' . $size . '" height="' . $size . '" ';
        }
        if (!empty($color)) {
            $style .= 'fill:' . $color . ';';
            $color = ' fill="' . $color . '"';
        }
        if (!empty($class)) {
            $class = ' class="' . $class . '"';
        }
        if (!empty($style)) {
            $style = ' style="' . $style . '"';
        }
        return '<svg' . $size . $color . $class . $style . '>'
            . '<use xlink:href="#' . $id . '"></use>' .
            '</svg>';
    }

    private static function createSvgSymbol(
        string $type,
        string $icon,
        string $path,
        string $size,
        string $class,
        string $color
    ) : string {

        $svgContents = file_get_contents($path);
        $svgDocument = new \DOMDocument();
        $svgDocument->loadXML($svgContents);

        // Used for caching and symbol identifier
        $id = 'fa-' . $type . '-' . $icon;

        // Create the symbol
        $symbolDocument = new \DOMDocument();
        $symbol = $symbolDocument->createElement('symbol');
        $symbol->setAttribute('id', $id);
        $symbol->setAttribute('viewBox', $svgDocument->documentElement->getAttribute('viewBox'));
        $symbolDocument->appendChild($symbol);

        // Get paths of font awesome SVG
        foreach ($svgDocument->documentElement->childNodes as $svgpath) {
            $iconPathsFragment = $symbolDocument->createDocumentFragment();
            $iconPathsFragment->appendXML($svgDocument->saveXML($svgpath));
            $symbol->appendChild($iconPathsFragment);
        }

        // Prepare symbol output
        $result = '<svg class="d-none">';
        foreach ($symbolDocument->childNodes as $childNode) {
            $result .= $symbolDocument->saveXML($childNode);
        }
        $result .= '</svg>';

        // Directly append a reference to this symbol (which is never displayed)
        $result .= self::buildSvgSymbolReference($id, (string)$size, $class, $color);

        // Add cache item and return the output
        self::$cache[$path] = $id;
        return $result;
    }
}
