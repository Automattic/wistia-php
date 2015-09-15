<?php
namespace Automattic\Wistia\Tests;

use BadMethodCallException;

trait ApiMethodsTraitTest {
    /**
     * Test Client::list_projects
     * @todo Fix it. Creates the project and then breaks the tests
     */
    public function test_list_projects() {
        $project  = $this->client->create_project( [ 'name' => 'Test Project' ] );
        $projects = $this->client->list_projects();

        $this->assertTrue( is_array( $projects ) );
        $this->assertObjectHasAttribute( 'id', $projects[0] );

        $this->delete_project( $project->hashedId );

    }

    /**
     * Test Client::show_project
     */
    public function test_show_project() {
        $project      = $this->client->create_project( [ 'name' => 'Test Project' ] );
        $show_project = $this->client->show_project( $project->hashedId );

        $this->assertInternalType( 'object', $show_project );
        $this->assertObjectHasAttribute( 'id', $show_project );

        $this->client->delete_project( $project->hashedId );
    }

    /**
     * Test Client::create_project
     */
    public function test_create_project() {
        $created_project = $this->client->create_project( [ 'name' => 'Test Project' ] );

        $this->assertInternalType( 'object', $created_project );
        $this->assertEquals( 'Test Project', $created_project->name );

        $this->client->delete_project( $created_project->hashedId );
    }

    /**
     * Test Client::update_project
     */
    public function test_update_project() {
        $project         = $this->client->create_project( [ 'name' => 'Test Project' ] );
        $updated_project = $this->client->update_project( $project->id, [ 'name' => 'Updated Test Project' ] );

        $this->assertInternalType( 'object', $updated_project );
        $this->assertEquals( $project->id, $updated_project->id );
        $this->assertEquals( 'Updated Test Project', $updated_project->name );

        $this->client->delete_project( $updated_project->hashedId );
    }

    /**
     * Test Client::delete_project
     */
    public function test_delete_project() {
        $project         = $this->client->create_project( [ 'name' => 'Test Project' ] );
        $deleted_project = $this->client->delete_project( $project->hashedId );

        $this->assertInternalType( 'object', $deleted_project );
        $this->assertEquals( $project->id, $deleted_project->id );
    }

    /**
     * Test Client::copy_project
     */
    public function test_copy_project() {
        $project        = $this->client->create_project( [ 'name' => 'Test Project' ] );
        $copied_project = $this->client->copy_project( $project->hashedId );

        $this->assertInternalType( 'object', $copied_project );
        $this->assertEquals( $project->name, $copied_project->name );

        $this->client->delete_project( $project->hashedId );
        $this->client->delete_project( $copied_project->hashedId );
    }

    public function test_list_sharings() {
        $project  = $this->client->create_project( [ 'name' => 'Test Project' ] );
        $sharings = $this->client->list_sharings( $project->hashedId );

        $this->assertInternalType( 'array', $sharings );
        $this->assertCount( 1, $sharings );
        $this->assertTrue( $sharings[0]->isAdmin );
        $this->assertEquals( $this->config['admin-email'], $sharings[0]->share->email );

        $this->client->delete_project( $project->hashedId );
    }
}
