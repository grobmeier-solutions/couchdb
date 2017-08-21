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

class CouchDesignDocument
{
    private $client;
    private $name;

    private $database;

    private $url;

    /**
     * CouchDatabase constructor.
     * @param Client $client
     * @param CouchDatabase $database
     * @param $name
     */
    public function __construct(Client $client, CouchDatabase $database, $name)
    {
        $this->client = $client;
        $this->database = $database;
        $this->name = $name;

        $this->url = sprintf("%s/_design/%s", $this->database->getName(), $this->name);
    }

    public function getUrl() {
        return $this->url;
    }

    public function exist()
    {
        return $this->client->head($this->url, ['exceptions' => false])->getStatusCode() == 200;
    }

    public function get()
    {
        return $this->client->get($this->url)->getBody()->getContents();
    }

    public function view($viewName) {
        return new CouchView($this->client, $this->url, $viewName);
    }

}
