<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Flex;

/**
 * @author François Pluchino <francois.pluchino@gmail.com>
 */
class FlexEvents
{
    /**
     * @Event("Symfony\Flex\FetchRecipesEvent")
     */
    const FETCH_RECIPES = 'flex.fetch_recipes';
}
