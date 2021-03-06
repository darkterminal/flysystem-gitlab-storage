<?php

namespace RoyVoetman\FlysystemGitlab;

use GuzzleHttp\Client as HttpClient;
use GuzzleHttp\Psr7\Response;

/**
 * Class GitlabAdapter
 *
 * @package RoyVoetman\FlysystemGitlab
 */
class Client
{
    const VERSION_URI = "/api/v4";
    
    /**
     * @var string
     */
    protected $personalAccessToken;
    
    /**
     * @var string
     */
    protected $projectId;
    
    /**
     * @var string
     */
    protected $branch;
    
    /**
     * @var string
     */
    protected $baseUrl;
    
    /**
     * Client constructor.
     *
     * @param  string  $personalAccessToken
     * @param  string  $projectId
     * @param  string  $branch
     * @param  string  $baseUrl
     */
    public function __construct(string $personalAccessToken, string $projectId, string $branch, string $baseUrl)
    {
        $this->personalAccessToken = $personalAccessToken;
        $this->projectId = $projectId;
        $this->branch = $branch;
        $this->baseUrl = $baseUrl;
    }
    
    /**
     * @param $path
     *
     * @return string
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function readRaw(string $path): string
    {
        $path = urlencode($path);
    
        $response = $this->request('GET', "files/$path/raw");
    
        return $this->responseContents($response, false);
    }
    
    /**
     * @param $path
     *
     * @return mixed|string
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function read($path)
    {
        $path = urlencode($path);
    
        $response = $this->request('GET', "files/$path");
    
        return $this->responseContents($response);
    }
    
    
    /**
     * @param  string  $path
     * @param  string  $contents
     * @param  string  $commitMessage
     * @param  bool  $override
     *
     * @return array
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function upload(string $path, string $contents, string $commitMessage, $override = false): array
    {
        $path = urlencode($path);
    
        $method = $override ? 'PUT' : 'POST';
    
        $response = $this->request($method, "files/$path", [
            'content'        => $contents,
            'commit_message' => $commitMessage
        ]);
        
        return $this->responseContents($response);
    }
    
    /**
     * @param  string  $path
     * @param $resource
     * @param  string  $commitMessage
     * @param  bool  $override
     *
     * @return array
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function uploadStream(string $path, $resource, string $commitMessage, $override = false): array
    {
        if (!is_resource($resource)) {
            throw new \InvalidArgumentException(sprintf('Argument must be a valid resource type. %s given.',
                gettype($resource)));
        }
    
        return $this->upload($path, stream_get_contents($resource), $commitMessage, $override);
    }
    
    /**
     * @param  string  $path
     * @param  string  $commitMessage
     *
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function delete(string $path, string $commitMessage)
    {
        $path = urlencode($path);
        
        $this->request('DELETE', "files/$path", [
            'commit_message' => $commitMessage
        ]);
    }
    
    /**
     * @param  string  $directory
     * @param  bool  $recursive
     *
     * @return mixed
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function tree(string $directory = null, bool $recursive = false): array
    {
        if ($directory === '/' || $directory === '') {
            $directory = null;
        }
        
        $response = $this->request('GET', 'tree', [
            'path'      => $directory,
            'recursive' => $recursive
        ]);
    
        return $this->responseContents($response);
    }
    
    /**
     * @return string
     */
    public function getPersonalAccessToken(): string
    {
        return $this->personalAccessToken;
    }
    
    /**
     * @param  string  $personalAccessToken
     */
    public function setPersonalAccessToken(string $personalAccessToken)
    {
        $this->personalAccessToken = $personalAccessToken;
    }
    
    /**
     * @return string
     */
    public function getProjectId(): string
    {
        return $this->projectId;
    }
    
    /**
     * @param  string  $projectId
     */
    public function setProjectId(string $projectId)
    {
        $this->projectId = $projectId;
    }
    
    /**
     * @return string
     */
    public function getBranch(): string
    {
        return $this->branch;
    }
    
    /**
     * @param  string  $branch
     */
    public function setBranch(string $branch)
    {
        $this->branch = $branch;
    }
    
    /**
     * @param  string  $method
     * @param  string  $uri
     * @param  array  $params
     *
     * @return \GuzzleHttp\Psr7\Response
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    private function request(string $method, string $uri, array $params = []): Response
    {
        $uri = ($method === 'GET') ? $this->buildUri($uri, $params) : $this->buildUri($uri);
    
        $params = ($method !== 'GET') ? ['form_params' => array_merge(['branch' => $this->branch], $params)] : [];
    
        $client = new HttpClient(['headers' => ['PRIVATE-TOKEN' => $this->personalAccessToken]]);
    
        return $client->request($method, $uri, $params);
    }
    
    /**
     * @param  string  $uri
     * @param $params
     *
     * @return string
     */
    private function buildUri(string $uri, array $params = []): string
    {
        $params = array_merge(['ref' => $this->branch], $params);
        
        $params = array_map('urlencode', $params);
        
        if(isset($params['path'])) {
            $params['path'] = urldecode($params['path']);
        }
        
        $params = http_build_query($params);
        
        $params = !empty($params) ? "?$params" : null;
    
        $baseUrl = rtrim($this->baseUrl, '/').self::VERSION_URI;
    
        return "{$baseUrl}/projects/{$this->projectId}/repository/{$uri}{$params}";
    }
    
    /**
     * @param  \GuzzleHttp\Psr7\Response  $response
     * @param  bool  $json
     *
     * @return mixed|string
     */
    private function responseContents(Response $response, $json = true)
    {
        $contents = $response->getBody()
            ->getContents();
        
        return ($json) ? json_decode($contents, true) : $contents;
    }
}