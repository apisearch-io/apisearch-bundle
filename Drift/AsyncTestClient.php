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

namespace Apisearch\Drift;

use Apisearch\Http\Client;
use Apisearch\Http\HttpClient;
use Apisearch\Http\RetryMap;
use Clue\React\Block;
use Drift\HttpKernel\AsyncKernel;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class AsyncTestClient.
 */
class AsyncTestClient extends Client implements HttpClient
{
    /**
     * @var AsyncKernel
     *
     * Async kernel
     */
    private $kernel;

    /**
     * TestClient constructor.
     *
     * @param AsyncKernel $kernel
     * @param string      $version
     * @param RetryMap    $retryMap
     */
    public function __construct(
        AsyncKernel $kernel,
        string $version,
        RetryMap $retryMap
    ) {
        $this->kernel = $kernel;

        parent::__construct(
            $version,
            $retryMap
        );
    }

    /**
     * Get a response given some parameters.
     * Return an array with the status code and the body.
     *
     * @param string $url
     * @param string $method
     * @param array  $query
     * @param array  $body
     * @param array  $server
     *
     * @return array
     */
    public function get(
        string $url,
        string $method,
        array $query = [],
        array $body = [],
        array $server = []
    ): array {
        $method = trim(strtolower($method));
        $requestParts = $this->buildRequestParts(
            $url,
            $query,
            $body,
            $server
        );

        $headersFormatted = [];
        foreach ($server as $key => $value) {
            $headersFormatted['HTTP_'.str_replace('-', '_', $key)] = $value;
        }

        $request = new Request(
            array_map('urldecode', $query),
            [],
            [],
            [],
            [],
            array_merge($headersFormatted, [
                'CONTENT_TYPE' => 'application/json',
                'HTTP_REFERER' => 'http://localhost',
            ]),
            json_encode($requestParts->getParameters()['json'])
        );

        $request->setMethod($method);
        $request->server->set('REQUEST_URI', $requestParts->getUrl());

        $promise = $this
            ->kernel
            ->handleAsync($request);

        $response = Block\await(
            $promise,
            $this
                ->kernel
                ->getContainer()
                ->get('reactphp.event_loop')
        );

        return [
            'code' => $response->getStatusCode(),
            'body' => json_decode($response->getContent(), true),
        ];
    }
}
