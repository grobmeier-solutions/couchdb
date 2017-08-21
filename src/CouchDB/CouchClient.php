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
use GuzzleHttp\Cookie\CookieJar;
use GuzzleHttp\Cookie\SetCookie;
use League\Uri\Schemes\Http;
use Psr\Http\Message\ResponseInterface;

class CouchClient
{
    private $username;
    private $password;
    private $baseUrl;
    private $authSession;

    /**
     * CouchClient constructor.
     *
     * @param $baseUrl
     * @param $username
     * @param $password
     */
    public function __construct($baseUrl, $username, $password)
    {
        $this->username = $username;
        $this->password = $password;
        $this->baseUrl = $baseUrl;
    }

    public function setCredentials($username, $password) {
        $this->username = $username;
        $this->password = $password;
    }

    public function setAuthSession($authSession)
    {
        $this->authSession = $authSession;
    }

    private function getClient($contentType = 'application/json')
    {
        $jar = new CookieJar();

        if ($this->authSession != null) {

            $uri = Http::createFromString($this->baseUrl);
            $cookieDomain = $uri->getHost();
            $jar->setCookie(SetCookie::fromString('AuthSession=' . $this->authSession . ';Path=/;Domain=' . $cookieDomain));
        }

        $config = [
            'base_uri' => $this->baseUrl,
            'headers' => [
                'Content-Type' => $contentType
            ],
            'cookies' => $jar
        ];

        if (!empty($this->username)) {
            $config['auth'] = [$this->username, $this->password];
        }

        return new Client($config);
    }

    /**
     * @param $name
     * @return CouchDatabase
     */
    public function database($name)
    {
        return new CouchDatabase($this->getClient(), $name);
    }

    /**
     * Creates a user in CouchDB
     *
     * @param $username
     * @param $password
     * @param string $type
     * @param array $roles
     * @param $salt string - the salt for CouchDB hashing
     * @return string
     */
    public function createUser($username, $password, $type = 'user', $roles = [], $salt = null)
    {
        $remotePath = '_users/org.couchdb.user:' . $username;

        $body = new \stdClass();
        $body->name = $username;

        if (!empty($salt)) {
            $body->password_sha = sha1($password . $salt);
            $body->salt = $salt;
        } else {
            $body->password = $password;
        }

        $body->roles = $roles;
        $body->type = $type;

        /** @var ResponseInterface $response */
        $response = $this->getClient()->request('PUT', $remotePath, [
            'body' => json_encode($body)
        ]);

        return $response->getBody()->getContents();
    }

    public function getSession()
    {
        /** @var ResponseInterface $response */
        $response = $this->getClient()->request('GET', '_session', ['http_errors' => false]);
        return $response->getBody()->getContents();
    }

    public function createSession($username, $password, $salt = null)
    {
        $body = new \stdClass();
        $body->name = $username;
        $body->password = $password;

        if (!empty($salt)) {
            // Cloudant and older versions of CouchDB need salting
            $formParams = [
                'name' => $username,
                'password' => $password
            ];
        } else {
            // Newer versions of CouchDB support server side hashing
            $formParams = [
                'name' => $username,
                'password' => $password
            ];
        }

        /** @var ResponseInterface $response */
        $response = $this->getClient('x-www-form-urlencoded')->request('POST', '_session', [
            'form_params' => $formParams
        ]);

        if ($response->getStatusCode() != 200) {
            throw new CouchException('Login failed.');
        }

        return [
            "cookie" => SetCookie::fromString(current($response->getHeader('Set-Cookie'))),
            "body" => $response->getBody()->getContents()
        ];
    }
}