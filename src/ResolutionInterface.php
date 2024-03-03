<?php

namespace Maduser\Craft\CraftyTemplates;

interface ResolutionInterface
{
    public function resolve(array $templatePaths = []): array;
}