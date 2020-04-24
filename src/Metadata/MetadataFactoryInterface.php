<?php

/*
 * This file is part of the broadway/broadway-saga package.
 *
 * (c) Qandidate.com <opensource@qandidate.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Broadway\Saga\Metadata;

use Broadway\Saga\MetadataInterface;

interface MetadataFactoryInterface
{
    /**
     * Creates and returns the Metadata for the given saga class
     *
     * @param string $saga
     *
     * @return MetadataInterface
     */
    public function create($saga): MetadataInterface;
}
