<?php
namespace Automattic\Wistia\Tests;

use BadMethodCallException;

trait ApiMethodsTraitTest {
    /**
     * Test Client::create_project
     */
    public function test_create_project() {
        $created_project = $this->client->create_project( [ 'name' => 'Test Project Function' ] );

        $this->assertInternalType( 'object', $created_project );
        $this->assertEquals( 'Test Project Function', $created_project->name );

        $this->client->delete_project( $created_project->hashedId );
    }

    /**
     * Test Client::delete_project
     */
    public function test_delete_project() {
        $project         = $this->client->create_project( [ 'name' => 'Test Project Delete' ] );
        $deleted_project = $this->client->delete_project( $project->hashedId );

        $this->assertInternalType( 'object', $deleted_project );
        $this->assertEquals( $project->id, $deleted_project->id );
    }

    /**
     * Test Client::list_projects
     */
    public function test_list_projects() {
        // Stop here and mark this test as incomplete.
        $this->markTestIncomplete(
          'This test has not been implemented yet.'
        );

        $projects = $this->client->list_projects();

        $this->assertTrue( is_array( $projects ) );
        $this->assertObjectHasAttribute( 'id', $projects[0] );

    }

    /**
     * Test Client::show_project
     */
    public function test_show_project() {
        $show_project = $this->client->show_project( $this->project->hashedId );

        $this->assertInternalType( 'object', $show_project );
        $this->assertObjectHasAttribute( 'id', $show_project );
    }

    /**
     * Test Client::update_project
     */
    public function test_update_project() {
        $project_name = $this->project->name;
        $updated_project = $this->client->update_project( $this->project->hashedId, [ 'name' => 'Updated Test Project' ] );

        $this->assertInternalType( 'object', $updated_project );
        $this->assertEquals( $this->project->id, $updated_project->id );
        $this->assertEquals( 'Updated Test Project', $updated_project->name );

        $this->client->update_project( $this->project->hashedId, [ 'name' => $project_name ] );
    }

    /**
     * Test Client::copy_project
     *
     * @depends test_delete_project
     */
    public function test_copy_project() {
        $copied_project = $this->client->copy_project( $this->project->hashedId );

        $this->assertInternalType( 'object', $copied_project );
        $this->assertEquals( $this->project->name, $copied_project->name );

        $this->client->delete_project( $copied_project->hashedId );
    }

    /**
     * Test Client::list_sharings
     */
    public function test_list_sharings() {
        $sharings = $this->client->list_sharings( $this->project->hashedId );

        $this->assertInternalType( 'array', $sharings );
        $this->assertCount( 1, $sharings );
        $this->assertTrue( $sharings[0]->isAdmin );
        $this->assertEquals( $this->config['admin-email'], $sharings[0]->share->email );
    }

    /**
     * Test Client::show_sharing
     *
     * @depends test_list_sharings
     */
    public function test_show_sharing() {
        $sharings   = $this->client->list_sharings( $this->project->hashedId );
        $sharing_id = $sharings[0]->id;
        $sharing    = $this->client->show_sharing( $this->project->hashedId, $sharing_id );

        $this->assertInternalType( 'object', $sharing );
        $this->assertTrue( $sharing->isAdmin );
        $this->assertEquals( $this->config['admin-email'], $sharing->share->email );
    }

    /**
     * Test Client::create_sharing
     */
    public function test_create_sharing() {
        $params = [
            'with'                  => 'test@automatticwistiatest.com',
            'sendEmailNotification' => 0
        ];

        $sharing = $this->client->create_sharing( $this->project->hashedId, $params );

        $this->assertInternalType( 'object', $sharing );
        $this->assertObjectHasAttribute( 'activation', $sharing );
    }

    /**
     * Test Client::update_sharing
     *
     * @depends test_list_sharings
     */
    public function test_update_sharing() {
        $sharings   = $this->client->list_sharings( $this->project->hashedId );
        $sharing_id = $sharings[0]->id;

        $sharing = $this->client->update_sharing( $this->project->hashedId, $sharing_id, [ 'isAdmin' => 0 ] );

        $this->assertFalse( $sharing->isAdmin );
    }

    /**
     * Test Client::delete_sharing
     *
     * @depends test_list_sharings
     */
    public function test_delete_sharing() {
        $sharings   = $this->client->list_sharings( $this->project->hashedId );
        $sharing_id = $sharings[0]->id;

        $sharing = $this->client->delete_sharing( $this->project->hashedId, $sharing_id );

        $this->assertInternalType( 'object', $sharing );
        $this->assertEquals( $sharing, $sharings[0] );
    }

    /**
     * Test Client::list_medias
     *
     * @depends test_create_media
     */
    public function test_list_medias() {
        $medias  = $this->client->list_medias( [ 'project_id' => $this->project->hashedId ] );

        $this->assertInternalType( 'array', $medias );
        $this->assertInternalType( 'object', $medias[0] );
        $this->assertEquals( $this->media->hashed_id, $medias[0]->hashed_id );
    }

    /**
     * Test Client::show_media
     */
    public function test_show_media() {
        $showed_media = $this->client->show_media( $this->media->hashed_id );

        $this->assertInternalType( 'object', $showed_media );
        $this->assertEquals( $this->media->hashed_id, $showed_media->hashed_id );
    }

    /**
     * Test Client::update_media
     */
    public function test_update_media() {
        $media_name    = $this->media->name;
        $updated_media = $this->client->update_media( $this->media->hashed_id, [ 'name' => 'A New Hope' ] );

        $this->assertInternalType( 'object', $updated_media );
        $this->assertEquals( $this->media->hashed_id, $updated_media->hashed_id );
        $this->assertEquals( 'A New Hope', $updated_media->name );

        $this->client->update_media( $this->media->hashed_id, [ 'name' => $media_name] );
    }

    /**
     * Test Client::delete_media
     *
     * @depends test_create_media
     */
    public function test_delete_media() {
        $media         = $this->client->create_media( $this->config['dummy-data']['image'], [ 'project_id' => $this->project->hashedId ] );
        $deleted_media = $this->client->delete_media( $media->hashed_id );

        $this->assertInternalType( 'object', $deleted_media );
        $this->assertEquals( $media->hashed_id, $deleted_media->hashed_id );
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

        $customizations = $this->client->create_customizations( $this->media->hashed_id, [ 'playerColor' => 'ffffcc' ] );

        $this->assertInternalType( 'object', $customizations );
        $this->assertObjectHasAttribute( 'playerColor', $customizations );
        $this->assertEquals( 'ffffcc', $customizations->playerColor );
    }

    /**
     * Test Client::show_customizations
     *
     * @depends test_create_customizations
     */
    public function test_show_customizations() {
        // Stop here and mark this test as incomplete.
        $this->markTestIncomplete(
          'This test has not been implemented yet.'
        );

        $customizations = $this->client->create_customizations( $this->media->hashed_id, [ 'playerColor' => 'ffffcc' ] );
        $customizations = $this->client->show_customizations( $this->media->hashed_id );

        $this->assertInternalType( 'object', $customizations );
        $this->assertObjectHasAttribute( 'playerColor', $customizations );
    }
}
