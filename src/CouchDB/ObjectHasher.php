<?php
/*
 *  Copyright 2016-2017 Grobmeier Solutions GmbH
 *
 *  Licensed under the Apache License, Version 2.0 (the "License");
 *  you may not use this file except in compliance with the License.
 *  You may obtain a copy of the License at
 *
 *  http://www.apache.org/licenses/LICENSE-2.0
 *
 *  Unless required by applicable law or agreed to in writing,
 *  software distributed under the License is distributed
 *  on an "AS IS" BASIS, WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND,
 *  either express or implied. See the License for the specific
 *  language governing permissions and limitations under the License.
 */
namespace GrobmeierSolutions\CouchDB;

/**
 * A class to work with hashing features related to CouchDB keys.
 */
class ObjectHasher
{
    /**
     * Creates a hash, excluding _hash, _id, _rev from the object.
     *
     * @param $object
     * @return string the hash for this object
     */
    public function createHash($object)
    {
        $hashObject = clone $object;

        $this->removeInternals($hashObject);

        return sha1(json_encode($hashObject));
    }

    /**
     * Removes _hash, _id and _rev
     *
     * Do not face this to public. It destroys object structures.
     *
     * Use it with cloned objects only.
     *
     * @param $object
     */
    private function removeInternals(&$object)
    {
        if (property_exists($object, 'r_hash')) {
            unset($object->r_hash);
        }

        if (property_exists($object, '_id')) {
            unset($object->_id);
        }

        if (property_exists($object, '_rev')) {
            unset($object->_rev);
        }
    }

    /**
     * Checks if two objects have the same hash.
     * If one object is null, the result will be always false.
     *
     * @param $one
     * @param $two
     * @return bool
     */
    public function equals($one, $two)
    {
        if ($one == null || $two == null) {
            return false;
        }

        return ($this->createHash($one) == $this->createHash($two));
    }
}
