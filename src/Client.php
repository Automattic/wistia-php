<?php
namespace SiRDanieL\Wistia;

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
     *
     * @var string
     */
    public $format;

    /**
     * API Password
     *
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

        $this->client = new HttpClient([
            'base_uri' => 'https://api.wistia.com/v1/'
        ]);
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
     * Send a GET request
     *
     * @return string
     */
    public function get( $endpoint, $query = [] ) {
        return $this->_make_request( 'GET', $endpoint, $query );
    }

    /**
     * Send a POST request
     *
     * @return string
     */
    public function post( $endpoint, $query = [] ) {
        return $this->_make_request( 'POST', $endpoint, $query );
    }

    /**
     * Send a PUT request
     *
     * @return string
     */
    public function put( $endpoint, $query = [] ) {
        return $this->_make_request( 'PUT', $endpoint, $query );
    }

    /**
     * Send a DELETE request
     *
     * @return string
     */
    public function delete( $endpoint, $query = [] ) {
        return $this->_make_request( 'DELETE', $endpoint, $query );
    }

    /**
     * Make a request
     *
     * @param  string        $type
     * @param  string        $endpoint
     * @param  array         $args
     * @return string
     * @access private
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
            $response = $this->client->request( $type, $endpoint . '.' . $this->format, $params );
            return json_decode( $response->getBody()->getContents() );
        } catch( TransferException $e ) {
            echo $e->getMessage() . ' - ' . $e->getResponse()->getReasonPhrase();
        }
    }
}

class WistiaException extends \Exception {}
