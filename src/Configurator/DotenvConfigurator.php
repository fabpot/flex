<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Flex\Configurator;

use Symfony\Flex\Lock;
use Symfony\Flex\Recipe;
use Symfony\Flex\Update\RecipeUpdate;

class DotenvConfigurator extends AbstractConfigurator
{
    public function configure(Recipe $recipe, $vars, Lock $lock, array $options = [])
    {
        foreach ($vars as $suffix => $vars) {
            $configurator = new EnvConfigurator($this->composer, $this->io, $this->options, $suffix);
            $configurator->configure($recipe, $vars, $lock, $options);
        }
    }

    public function unconfigure(Recipe $recipe, $vars, Lock $lock)
    {
        foreach ($vars as $suffix => $vars) {
            $configurator = new EnvConfigurator($this->composer, $this->io, $this->options, $suffix);
            $configurator->unconfigure($recipe, $vars, $lock);
        }
    }

    public function update(RecipeUpdate $recipeUpdate, array $originalConfig, array $newConfig): void
    {
        foreach ($originalConfig as $suffix => $vars) {
            $configurator = new EnvConfigurator($this->composer, $this->io, $this->options, $suffix);
            $configurator->update($recipeUpdate, $vars, $newConfig[$suffix] ?? []);
        }

        foreach ($newConfig as $suffix => $vars) {
            if (!isset($originalConfig[$suffix])) {
                $configurator = new EnvConfigurator($this->composer, $this->io, $this->options, $suffix);
                $configurator->update($recipeUpdate, [], $vars);
            }
        }
    }
}
