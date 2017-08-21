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

use GuzzleHttp\Client;
use Psr\Http\Message\ResponseInterface;

class CouchDatabase
{
    private $client;
    private $name;

    /**
     * CouchDatabase constructor.
     *
     * @param Client $client
     * @param $name
     */
    public function __construct(Client $client, $name)
    {
        $this->client = $client;
        $this->name = $name;
    }

    /**
     * Returns a document by it's couchdb id
     * @param $id string the couchdb id
     * @return string the json for the entity
     */
    public function getById($id)
    {
        /** @var ResponseInterface $response */
        $response = $this->client->get($this->name . '/'. $id);
        return $response->getBody()->getContents();
    }

    public function exists($id)
    {
        /** @var ResponseInterface $response */
        try {
            $this->client->head($this->name . '/' . $id);
            return true;
        } catch(\Exception $e) {
            return false;
        }
    }

    public function getItems($limit, $skip = 0)
    {
        /** @var ResponseInterface $response */
        $response = $this->client->get($this->name . '/_all_docs?include_docs=true&limit='. $limit . '&skip=' . $skip);
        return $response->getBody()->getContents();
    }

    /**
     * Creates a new object in CouchDB. The object needs to be JSON serializable
     *
     * @param $object
     * @return string
     */
    public function create($object)
    {
        $response = $this->client->request('POST', '/' . $this->name, [
            'body' => json_encode($object)
        ]);

        return $response->getBody()->getContents();
    }

    public function update(CouchObject $object)
    {
        $response = $this->client->request('PUT', '/' . $this->name . '/' .$object->_id, [
            'body' => json_encode($object)
        ]);

        return $response->getBody()->getContents();
    }

    public function delete($objectId, $revisionId)
    {
        $response = $this->client->request('DELETE', '/' . $this->name . '/' .$objectId.'?rev='.$revisionId);
        return $response->getBody()->getContents();
    }

    /**
     * @param $name string the name of the design document
     * @return CouchDesignDocument
     */
    public function getDesignDocument($name)
    {
        return new CouchDesignDocument($this->client, $this, $name);
    }

    public function createDesignDocument($name, $content)
    {
        $remotePath = '/' . $this->name . '/_design/' . $name;

        /** @var ResponseInterface $response */
        $response = $this->client->request('PUT', $remotePath, [
            'body' => $content
        ]);

        return $response->getBody()->getContents();
    }

    public function replicateDesignDocument($from, $target, $dDoc)
    {
        $replicaSet = new \stdClass();
        $replicaSet->source = $from . '/' . $this->name;
        $replicaSet->target = $target;
        $replicaSet->doc_ids = [ $dDoc ];

        $client = new Client([
            'base_uri' => $from,
            'headers' => [
                'Content-Type' => 'application/json'
            ]
        ]);

        $response = $client->request('POST', '/_replicate', [
            'body' => json_encode($replicaSet)
        ]);

        return $response->getBody()->getContents();
    }

    /**
     * @return mixed
     */
    public function getName()
    {
        return $this->name;
    }
}