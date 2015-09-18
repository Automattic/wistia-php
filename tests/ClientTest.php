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
     * Test project
     * @var object
     */
    public $project;

    /**
     * Test media
     * @var object
     */
    public $media;

    /**
     * Test video
     * @var object
     */
    public $video;

    /**
     * Test captions
     * @var object
     */
    public $captions;

    /**
     * Setup the Client
     */
    public function setUp() {
        global $test_config;

        $this->includes();

        $this->config   = $test_config;
        $this->client   = new Client( $this->config );
        $this->project  = $this->client->create_project( [ 'name' => 'Test Project' ] );
        $this->media    = $this->client->create_media( $this->config['dummy-data']['image'], [ 'project_id' => $this->project->hashedId ] );
        $this->video    = $this->client->create_media( $this->config['dummy-data']['video'], [ 'project_id' => $this->project->hashedId ] );
        $this->captions = $this->client->create_captions( $this->video->hashed_id, [ 'caption_file' => file_get_contents( $this->config['dummy-data']['captions'] ), 'language' => 'eng' ] );
    }

    public function tearDown() {
        $this->client->delete_project( $this->project->hashedId );
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

    /**
     * Test Client::create_media
     */
    public function test_create_media() {
        $media   = $this->client->create_media( $this->config['dummy-data']['image'], [ 'project_id' => $this->project->hashedId ] );

        $this->assertInternalType( 'object', $media );
    }
}
