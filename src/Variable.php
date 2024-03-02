<?php
namespace Maduser\Craft\CraftyTemplates;

use craft\base\ElementInterface;
use Maduser\Craft\CraftyTemplates\EntryHelper;
use ReflectionClass;

class Variable
{
    /**
     * @param ElementInterface $element
     * @param string           $tabName
     *
     * @return array
     */
    public function fieldHandlesFromTab(ElementInterface $element, string $tabName): array
    {
        return EntryHelper::getEntryFieldHandlesByTab($element, $tabName);
    }

    /**
     * @param $field
     *
     * @return string
     * @throws \ReflectionException
     */
    public function fieldType($field): string
    {
        return $this->decamelize((new ReflectionClass($field))->getShortName());
    }

    /**
     * @param string $path
     *
     * @return string
     */
    public function criticalCss(string $path): string
    {
        return file_exists($path) ? file_get_contents($path) : '';
    }

    /**
     * @param $string
     *
     * @return string
     */
    public function decamelize($string): string
    {
        return strtolower(preg_replace(['/([a-z\d])([A-Z])/', '/([^-])([A-Z][a-z])/'], '$1-$2', $string));
    }
}
