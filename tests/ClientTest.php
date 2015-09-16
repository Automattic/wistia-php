<?php
namespace Automattic\Wistia\Tests;

use GuzzleHttp\Client as HttpClient;
use GuzzleHttp\Exception\TransferException;
use Automattic\Wistia\Client;

class ClientTest extends \PHPUnit_Framework_TestCase {
    use ApiMethodsTraitTest;

    /**
     * Test config
     * @var array
     */
    public $config;

    /**
     * Setup the Client
     */
    public function setUp() {
        global $test_config;

        $this->includes();

        $this->config = $test_config;
        $this->client = new Client( $this->config );
    }

    /**
     * Include needed files
     */
    public function includes() {
        include __DIR__ . '/config.php';
    }

    /**
     * Test Client::get_client
     */
    public function test_get_client() {
        $this->assertEquals( $this->client, $this->client->get_client() );
    }

    /**
     * Test Client::get_token
     */
    public function test_get_token() {
        $this->assertEquals( $this->config['token'], $this->client->get_token() );
    }
}
