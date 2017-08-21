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

class CouchDesignDocument
{
    private $client;
    private $name;

    private $database;

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
    }

    public function exist()
    {
        $url = sprintf("%s/_design/%s", $this->database->getName(), $this->name);
        return $this->client->head($url, ['exceptions' => false])->getStatusCode() == 200;
    }

    public function get()
    {
        $url = sprintf("%s/_design/%s", $this->database->getName(), $this->name);
        return $this->client->get($url)->getBody()->getContents();
    }

    public function aggregateView($name, $keys = null)
    {
        return $this->view($name, $keys, null, null, true, true);
    }

    public function aggregateRangeView($name, $startKeys = null, $endKeys = null)
    {
        return $this->rangeView($name, $startKeys, $endKeys, null, null, true, true);
    }

    /**
     * @param $name string the name of the view
     * @param null|array|string|int $key the keys
     * @param null|int $limit the max limit of the results
     * @param null|int $skip skip $x results
     * @param bool $reduce
     * @param bool $group
     * @return string
     */
    public function view($name, $key = null, $limit = null, $skip = null, $reduce = false, $group = false)
    {
        $params = [];

        if ($key != null) {
            $params['keys'] = 'key=' . json_encode($key);
        }

        if ($limit != null) {
            $params['limit'] = 'limit=' . $limit;
        }

        if ($skip != null) {
            $params['skip'] = 'skip=' . $skip;
        }

        $params['reduce'] = 'reduce=' . (($reduce) ? 'true' : 'false');
        $params['group'] =  'group='  . (($group) ? 'true' : 'false');
        $paramString = '?' . implode('&', $params);

        $url = sprintf('%s/_design/%s/_view/%s%s', $this->database->getName(), $this->name, $name, $paramString);

        /** @var ResponseInterface $response */
        $response = $this->client->get($url);

        return $response->getBody()->getContents();
    }

    /**
     * @param $name string the name of the view
     * @param null $startKeys start filtering keys
     * @param null $endKeys end filtering keys
     * @param null|int $limit the max limit of the results
     * @param null|int $skip skip $x results
     * @param bool $reduce
     * @param bool $group
     * @return string
     * @internal param array|int|null|string $key the keys
     */
    public function rangeView($name, $startKeys = null, $endKeys = null, $limit = null, $skip = null, $reduce = false, $group = false)
    {
        $params = [];

        if ($startKeys != null) {
            $params['startKeys'] = 'startKeys=' . json_encode($startKeys);
        }

        if ($endKeys != null) {
            $params['endKeys'] = 'endKeys=' . json_encode($endKeys);
        }

        if ($limit != null) {
            $params['limit'] = 'limit=' . $limit;
        }

        if ($skip != null) {
            $params['skip'] = 'skip=' . $skip;
        }

        $params['reduce'] = 'reduce=' . (($reduce) ? 'true' : 'false');
        $params['group'] =  'group='  . (($group) ? 'true' : 'false');
        $paramString = '?' . implode('&', $params);

        $url = sprintf('%s/_design/%s/_view/%s%s', $this->database->getName(), $this->name, $name, $paramString);

        /** @var ResponseInterface $response */
        $response = $this->client->get($url);

        return $response->getBody()->getContents();
    }
}
