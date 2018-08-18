<?php

/*
 * This file is part of the Apisearch Bundle.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * Feel free to edit as you please, and have fun.
 *
 * @author Marc Morera <yuhu@mmoreram.com>
 */

declare(strict_types=1);

namespace Apisearch\Tests\Functional;

use Apisearch\Model\Item;
use Apisearch\Model\ItemUUID;
use Apisearch\Transformer\ReadTransformer;
use Apisearch\Transformer\WriteTransformer;

/**
 * Class ProductBothTransformer.
 */
class ProductBothTransformer implements WriteTransformer, ReadTransformer
{
    /**
     * Is an indexable object.
     *
     * @param mixed $object
     *
     * @return bool
     */
    public function isValidObject($object): bool
    {
        return true;
    }

    /**
     * Create item by object.
     *
     * @param mixed $object
     *
     * @return Item
     */
    public function toItem($object): Item
    {
        return Item::create($this->toItemUUID($object));
    }

    /**
     * Create item UUID by object.
     *
     * @param mixed $object
     *
     * @return ItemUUID
     */
    public function toItemUUID($object): ItemUUID
    {
        return new ItemUUID('1', 'a');
    }

    /**
     * The item should be converted by this transformer.
     *
     * @param Item $item
     *
     * @return bool
     */
    public function isValidItem(Item $item): bool
    {
        return true;
    }

    /**
     * Create object by item.
     *
     * @param Item $item
     *
     * @return mixed
     */
    public function fromItem(Item $item)
    {
        return [
        ];
    }
}
