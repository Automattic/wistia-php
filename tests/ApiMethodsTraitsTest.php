<?php
namespace Automattic\Wistia\Tests;

use BadMethodCallException;

trait ApiMethodsTraitTest {
    /**
     * Test Client::list_projects
     */
    public function test_list_projects() {
        // Stop here and mark this test as incomplete.
        $this->markTestIncomplete(
          'This test has not been implemented yet.'
        );

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
        $updated_project = $this->client->update_project( $project->hashedId, [ 'name' => 'Updated Test Project' ] );

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

    /**
     * Test Client::list_sharings
     */
    public function test_list_sharings() {
        $project  = $this->client->create_project( [ 'name' => 'Test Project' ] );
        $sharings = $this->client->list_sharings( $project->hashedId );

        $this->assertInternalType( 'array', $sharings );
        $this->assertCount( 1, $sharings );
        $this->assertTrue( $sharings[0]->isAdmin );
        $this->assertEquals( $this->config['admin-email'], $sharings[0]->share->email );

        $this->client->delete_project( $project->hashedId );
    }

    /**
     * Test Client::show_sharing
     */
    public function test_show_sharing() {
        $project    = $this->client->create_project( [ 'name' => 'Test Project' ] );
        $sharings   = $this->client->list_sharings( $project->hashedId );
        $sharing_id = $sharings[0]->id;
        $sharing    = $this->client->show_sharing( $project->hashedId, $sharing_id );

        $this->assertInternalType( 'object', $sharing );
        $this->assertTrue( $sharing->isAdmin );
        $this->assertEquals( $this->config['admin-email'], $sharing->share->email );

        $this->client->delete_project( $project->hashedId );
    }

    /**
     * Test Client::create_sharing
     */
    public function test_create_sharing() {
        $project = $this->client->create_project( [ 'name' => 'Test Project' ] );

        $params = [
            'with'                  => 'test@automatticwistiatest.com',
            'sendEmailNotification' => 0
        ];

        $sharing = $this->client->create_sharing( $project->hashedId, $params );

        $this->assertInternalType( 'object', $sharing );
        $this->assertObjectHasAttribute( 'activation', $sharing );

        $this->client->delete_project( $project->hashedId );
    }

    /**
     * Test Client::update_sharing
     */
    public function test_update_sharing() {
        $project    = $this->client->create_project( [ 'name' => 'Test Project' ] );
        $sharings   = $this->client->list_sharings( $project->hashedId );
        $sharing_id = $sharings[0]->id;

        $sharing = $this->client->update_sharing( $project->hashedId, $sharing_id, [ 'isAdmin' => 0 ] );

        $this->assertFalse( $sharing->isAdmin );

        $this->client->delete_project( $project->hashedId );
    }

    /**
     * Test Client::delete_sharing
     */
    public function test_delete_sharing() {
        $project    = $this->client->create_project( [ 'name' => 'Test Project' ] );
        $sharings   = $this->client->list_sharings( $project->hashedId );
        $sharing_id = $sharings[0]->id;

        $sharing = $this->client->delete_sharing( $project->hashedId, $sharing_id );

        $this->assertInternalType( 'object', $sharing );
        $this->assertEquals( $sharing, $sharings[0] );

        $this->client->delete_project( $project->hashedId );
    }

    /**
     * Test Client::list_medias
     */
    public function test_list_medias() {
        $project = $this->client->create_project( [ 'name' => 'Test Project' ] );
        $media   = $this->client->create_media( $this->config['dummy-data']['image'], [ 'project_id' => $project->hashedId ] );
        $medias  = $this->client->list_medias( [ 'project_id' => $project->hashedId ] );

        $this->assertInternalType( 'array', $medias );
        $this->assertCount( 1, $medias );
        $this->assertInternalType( 'object', $medias[0] );
        $this->assertEquals( $media->hashed_id, $medias[0]->hashed_id );

        $this->client->delete_project( $project->hashedId );
    }

    /**
     * Test Client::show_media
     */
    public function test_show_media() {
        $project      = $this->client->create_project( [ 'name' => 'Test Project' ] );
        $media        = $this->client->create_media( $this->config['dummy-data']['image'], [ 'project_id' => $project->hashedId ] );
        $showed_media = $this->client->show_media( $media->hashed_id );

        $this->assertInternalType( 'object', $showed_media );
        $this->assertEquals( $media->hashed_id, $showed_media->hashed_id );

        $this->client->delete_project( $project->hashedId );
    }

    /**
     * Test Client::update_media
     */
    public function test_update_media() {
        $project       = $this->client->create_project( [ 'name' => 'Test Project' ] );
        $media         = $this->client->create_media( $this->config['dummy-data']['image'], [ 'project_id' => $project->hashedId ] );
        $updated_media = $this->client->update_media( $media->hashed_id, [ 'name' => 'A New Hope' ] );

        $this->assertInternalType( 'object', $updated_media );
        $this->assertEquals( $media->hashed_id, $updated_media->hashed_id );
        $this->assertEquals( 'A New Hope', $updated_media->name );

        $this->client->delete_project( $project->hashedId );
    }

    /**
     * Test Client::delete_media
     */
    public function test_delete_media() {
        $project       = $this->client->create_project( [ 'name' => 'Test Project' ] );
        $media         = $this->client->create_media( $this->config['dummy-data']['image'], [ 'project_id' => $project->hashedId ] );
        $deleted_media = $this->client->delete_media( $media->hashed_id );

        $this->assertInternalType( 'object', $deleted_media );
        $this->assertEquals( $media->hashed_id, $deleted_media->hashed_id );

        $this->client->delete_project( $project->hashedId );
    }

    /**
     * Test Client::show_account
     */
    public function test_show_account() {
        $account = $this->client->show_account();

        $this->assertInternalType( 'object', $account );
        $this->assertObjectHasAttribute( 'id', $account );
        $this->assertObjectHasAttribute( 'name', $account );
        $this->assertObjectHasAttribute( 'url', $account );
    }

    /**
     * Test Client::create_customizations
     */
    public function test_create_customizations() {
        // Stop here and mark this test as incomplete.
        $this->markTestIncomplete(
          'This test has not been implemented yet.'
        );

        $project        = $this->client->create_project( [ 'name' => 'Test Project' ] );
        $media          = $this->client->create_media( $this->config['dummy-data']['image'], [ 'project_id' => $project->hashedId ] );
        $customizations = $this->client->create_customizations( $media->hashed_id, [ 'playerColor' => 'ffffcc' ] );

        $this->assertInternalType( 'object', $customizations );
        $this->assertObjectHasAttribute( 'playerColor', $customizations );
        $this->assertEquals( 'ffffcc', $customizations->playerColor );

        $this->client->delete_project( $project->hashedId );
    }

    /**
     * Test Client::show_customizations
     */
    public function test_show_customizations() {
        // Stop here and mark this test as incomplete.
        $this->markTestIncomplete(
          'This test has not been implemented yet.'
        );

        $project        = $this->client->create_project( [ 'name' => 'Test Project' ] );
        $media          = $this->client->create_media( $this->config['dummy-data']['image'], [ 'project_id' => $project->hashedId ] );
        $customizations = $this->client->create_customizations( $media->hashed_id, [ 'playerColor' => 'ffffcc' ] );
        $customizations = $this->client->show_customizations( $media->hashed_id );

        $this->assertInternalType( 'object', $customizations );
        $this->assertObjectHasAttribute( 'playerColor', $customizations );

        $this->client->delete_project( $project->hashedId );
    }
}
