<?php
/**
 * REST API class
 */

namespace Institute_Management\API;

class REST_API {
    public function __construct() {
        add_action('rest_api_init', array($this, 'register_routes'));
    }
    
    public function register_routes() {
        // REST API endpoints for students and staff
    }
} 