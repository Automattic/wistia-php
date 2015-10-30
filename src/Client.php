<?php
namespace Automattic\Wistia;

use GuzzleHttp\Client as HttpClient;
use GuzzleHttp\Exception\TransferException;

class Client {
    use Traits\ApiMethodsTrait;

    /**
     * Wrapper version
     */
    const VERSION = '1.0.0';

    /**
     * The HTTP client
     * @var object
     */
    public $client;

    /**
     * Response format
     * @var string
     */
    public $format;

    /**
     * The response code of the last request
     * @var int
     */
    public $last_response_code;

    /**
     * API Password
     * @var    string
     * @access private
     */
    private $_token;

    /**
     * Init the HTTP Client
     *
     * @param array $params
     */
    public function __construct( $params ) {
        /**
         * Default params to pass to the Client
         */
        $defaults = [
            'format' => 'json'
        ];

        $params = array_merge( $defaults, $params );

        if ( ! isset( $params['token'] ) ) {
            throw new WistiaException( 'Client error: 401 - Token is required', 401 );
        }

        $this->format = $params['format'];
        $this->_token = $params['token'];

        $this->client = new HttpClient( [
            'base_uri' => 'https://api.wistia.com/v1/'
        ] );

        $this->upload_client = new HttpClient( [
            'base_uri' => 'https://upload.wistia.com/'
        ] );
    }

    /**
     * Prevent unserialization
     */
    public function __wakeup() {
        trigger_error( sprintf( 'Unsupported unserialization of %s', __CLASS__ ), E_USER_ERROR );
    }

    /**
     * Returns the client
     *
     * @return object
     */
    public function get_client() {
        return $this;
    }

    /**
     * Get the token
     *
     * @return string
     */
    public function get_token() {
        return $this->_token;
    }

    /**
     * Send a GET request
     *
     * @return string
     * @codeCoverageIgnore
     */
    public function get( $endpoint, $query = [] ) {
        return $this->_make_request( 'GET', $endpoint, $query );
    }

    /**
     * Send a POST request
     *
     * @return string
     * @codeCoverageIgnore
     */
    public function post( $endpoint, $query = [] ) {
        return $this->_make_request( 'POST', $endpoint, $query );
    }

    /**
     * Send a PUT request
     *
     * @return string
     * @codeCoverageIgnore
     */
    public function put( $endpoint, $query = [] ) {
        return $this->_make_request( 'PUT', $endpoint, $query );
    }

    /**
     * Send a DELETE request
     *
     * @return string
     * @codeCoverageIgnore
     */
    public function delete( $endpoint, $query = [] ) {
        return $this->_make_request( 'DELETE', $endpoint, $query );
    }

    /**
     * Create a new media. Wistia handles upload differently than other methods.
     * The API endpoint is different and the parameters are completely different.
     * We need a new Client to handle this kind of requests.
     *
     * @param  string $file
     * @param  array  $query
     * @return object
     */
    public function create_media( $file, $query = [] ) {
        if ( empty( $file ) || ! file_exists( $file ) ) {
            throw new WistiaException( 'Client error: A valid file path is required to create a media.' );
        }

        $params = [
            'headers' => [
                'User-Agent'    => 'Wistia PHP Wrapper/' . self::VERSION
            ],
            'multipart' => [
                [
                    'name' => 'api_password',
                    'contents' => $this->_token
                ],
                [
                    'name' => 'file',
                    'contents' => fopen( $file, 'r' )
                ]
            ]
        ];

        if ( ! empty( $query ) ) {
            foreach( $query as $name => $value ) {
                $data = [ 'name' => $name, 'contents' => $value ];

                if ( ! in_array( $data, $params['multipart'] ) ) {
                    array_push( $params['multipart'], $data );
                }
            }
        }

        try {
            $response                 = $this->upload_client->request( 'POST', '', $params );
            $this->last_response_code = $response->getStatusCode();

            if ( $response->getStatusCode() === 200 || $response->getStatusCode() === 400 ) {
                return json_decode( $response->getBody()->getContents() );
            } else {
                // Error 401 - API password probably wrong. Returns text/html
                return $response->getBody()->getContents();
            }
        } catch( TransferException $e ) {
            return $e->getCode();
        }
    }

    /**
     * Make a request
     *
     * @param  string        $type
     * @param  string        $endpoint
     * @param  array         $args
     * @return string
     * @access private
     * @codeCoverageIgnore
     */
    private function _make_request( $type, $endpoint, $query = [] ) {
        $params = [
            'headers' => [
                'Authorization' => 'Basic ' . base64_encode( 'api:' . $this->_token ),
                'Accept'        => 'application/' . $this->format,
                'User-Agent'    => 'Wistia PHP Wrapper/' . self::VERSION
            ],
        ];

        if ( ! empty( $query ) && empty( $params['query'] ) ) {
            $params['query'] = $query;
        }

        try {
            $response                 = $this->client->request( $type, $endpoint . '.' . $this->format, $params );
            $this->last_response_code = $response->getStatusCode();

            return json_decode( $response->getBody()->getContents() );
        } catch( TransferException $e ) {
            return $e->getCode();
        }
    }
}

class WistiaException extends \Exception {}
