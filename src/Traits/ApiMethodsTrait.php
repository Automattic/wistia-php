<?php
namespace Automattic\Wistia\Traits;

use BadMethodCallException;

trait ApiMethodsTrait {

    /**
     * Methods allowed for this Trait
     * @var array
     */
    protected $_methods = [
        // Projects
        'list_projects'         => [ 'get', 'projects' ],
        'show_project'          => [ 'get', 'projects/%s' ],
        'create_project'        => [ 'post', 'projects' ],
        'update_project'        => [ 'put', 'projects/%s' ],
        'delete_project'        => [ 'delete', 'projects/%s' ],
        'copy_project'          => [ 'post', 'projects/%s/copy' ],

        // Project Sharings
        'list_sharings'         => [ 'get', 'projects/%s/sharings' ],
        'show_sharing'          => [ 'get', 'projects/%s/sharings/%d' ],
        'create_sharing'        => [ 'post', 'projects/%s/sharings' ],
        'update_sharing'        => [ 'put', 'projects/%s/sharings/%d' ],
        'delete_sharing'        => [ 'delete', 'projects/%s/sharings/%d' ],

        // Medias
        'list_medias'           => [ 'get', 'medias' ],
        'show_media'            => [ 'get', 'medias/%s' ],
        'update_media'          => [ 'put', 'medias/%s' ],
        'delete_media'          => [ 'delete', 'medias/%s' ],
        'copy_media'            => [ 'post', 'medias/%s/copy' ],
        'stats_media'           => [ 'get', 'medias/%s/stats' ],

        // Account
        'show_account'          => [ 'get', 'account' ],

        // Media Customizations
        'show_customizations'   => [ 'get', 'medias/%s/customizations' ],
        'create_customizations' => [ 'post', 'medias/%s/customizations' ],
        'update_customizations' => [ 'put', 'medias/%s/customizations' ],
        'delete_customizations' => [ 'delete', 'medias/%s/customizations' ],

        // Media Captions
        'list_captions'         => [ 'get', 'medias/%s/captions' ],
        'show_captions'         => [ 'get', 'medias/%s/captions/%s' ],
        'create_captions'       => [ 'post', 'medias/%s/captions' ],
        'update_captions'       => [ 'put', 'medias/%s/captions/%s' ],
        'delete_captions'       => [ 'delete', 'medias/%s/captions/%s' ],
    ];

    abstract public function get_client();

    /**
     * Call a defined method
     *
     * @param  string $method
     * @param  array $params
     * @return array
     */
    public function __call( $method, $params ) {
        if ( null === $signature = $this->_get_method_signature( $method ) ) {
            throw new BadMethodCallException( 'Method ' . $method . ' not found on ' . get_class() . '.', 500 );
        }

        preg_match_all( '/\%/', $signature[1], $replacements );

        $replacement_count = isset( $replacements[0] ) ? count( $replacements[0] ) : 0;
        $replacement_params = array_splice( $params, 0, $replacement_count );
        array_unshift( $replacement_params, $signature[1] );

        $path = call_user_func_array( 'sprintf', $replacement_params );
        array_unshift( $params, $path );

        return call_user_func_array( [ $this->get_client(), $signature[0] ], $params );
    }

    /**
     * Check if a method exists and return its name and params
     *
     * @param  string $name
     * @return array|null
     * @access private
     */
    private function _get_method_signature( $method ) {
        $valid_method = isset( $this->_methods[ $method ] ) &&
                        is_array( $this->_methods[ $method ] ) &&
                        count( $this->_methods[ $method ] ) >= 2;

        if ( $valid_method ) {
            return $this->_methods[ $method ];
        }

        return null;
    }
}
