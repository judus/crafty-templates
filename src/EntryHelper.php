<?php

namespace Maduser\Craft\CraftyTemplates;

use craft\base\ElementInterface;
use craft\base\FieldInterface;
use craft\elements\Entry;
use craft\fieldlayoutelements\CustomField;
use craft\fields\Assets;
use craft\fields\Matrix;
use craft\models\FieldLayoutTab;
use Illuminate\Support\Collection;

/**
 * Helps to retrieve field objects and values of one or more entries
 */
class EntryHelper
{
    /**
     * @var array
     */
    public static array $tabFields = [];

    /**
     * Get the given Entry's Tab by name
     *
     * @param Entry  $entry
     * @param string $tabName
     *
     * @return FieldLayoutTab|null
     */
    public static function getTabByName(Entry $entry, string $tabName): ?FieldLayoutTab
    {
        $tabs = $entry->getFieldLayout()->getTabs();

        foreach ($tabs as $tab) {
            if ($tab->name == $tabName) {
                return $tab;
            }
        }

        return null;
    }

    /**
     * Get the given Entry's field objects by tab name
     *
     * @param $entry
     * @param $tabName
     *
     * @return array
     */
    public static function getTabFields($entry, $tabName): array
    {
        $key = $entry->section->handle . '__' . $tabName;
        $fields = [];

        if (isset(self::$tabFields[$key])) {
            $fields = self::$tabFields[$key];
        } else {

            if (!$tab = self::getTabByName($entry, $tabName)) {
                return [];
            }

            foreach ($tab->elements as $element) {
                if ($element instanceof CustomField) {
                    $field = $element->getField();
                    $fields[$field->handle] = $field;
                }
            }

            self::$tabFields[$key] = $fields;
        }

        return $fields;
    }

    /**
     * Get the field value of an entry as array
     *
     * @param Entry          $entry
     * @param FieldInterface $field
     *
     * @return array|Collection|mixed|null
     */
    public static function getFieldValue(Entry $entry, FieldInterface $field): mixed
    {
        if ($field instanceof Assets) {
            $assets = [];

            foreach ($entry->{$field->handle}->all() as $asset) {
                $assets[] = $asset->toArray();
            }

            return $assets;
        }

        if ($field instanceof Matrix) {

            $values = [];

            foreach ($entry->{$field->handle}->all() as $block) {
                $values[] = $block->toArray();
            }

            return $values;
        }

        return $entry->{$field->handle};
    }

    /**
     * Get the given Entry's field values by tab name
     *
     * @param Entry  $entry
     * @param string $tabName
     *
     * @return array|null
     */
    public static function getEntryFieldsByTab(Entry $entry, string $tabName): ?array
    {
        $values = ['title' => $entry->title];

        $fields = self::getTabFields($entry, $tabName);

        foreach ($fields as $handle => $field) {
            $values[$handle] = self::getFieldValue($entry, $field);
        }

        return $values;
    }

    /**
     * Get the given Entry's field values
     *
     * @param Entry  $entry
     *
     * @return array|null
     */
    public static function getEntryFields(Entry $entry): ?array
    {
        $values = ['title' => $entry->title];

        $fields = $entry->getFieldLayout()->getCustomFields();

        foreach ($fields as $field) {
            $values[$field->handle] = self::getFieldValue($entry, $field);
        }

        return $values;
    }

    /**
     * Get the given Entry's field handles by tab name
     *
     * @param Entry  $entry
     * @param string $tabName
     *
     * @return array|null
     */
    public static function getEntryFieldHandlesByTab(ElementInterface $entry, string $tabName): ?array
    {
        return array_keys(self::getTabFields($entry, $tabName));
    }

    /**
     * Get the field values of each given entries by tab name
     *
     * @param array  $entries
     * @param string $tabName
     *
     * @return array|null
     */
    public static function getEntriesFieldValuesByTab(array $entries, string $tabName): ?array
    {
        $entriesFields = [];
        foreach ($entries as $entry) {
            $entriesFields[] = self::getEntryFieldsByTab($entry, $tabName);
        }

        return $entriesFields;
    }

}