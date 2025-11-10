<?php

namespace app\models;

use Yii;
use yii\helpers\Url;

abstract class GraphConnection {

    protected $protocol;

    public function __construct($annotation_db) {
        self::connect($annotation_db);
    }
    
    public function connect($annotation_db) {

        // Create connection class and specify target host and port
        // TODO: changed Socket to StreamSocket to support SSL - CHECK IF AVANTGRAPH WORKS WITH THIS
        $conn = new \Bolt\connection\StreamSocket($annotation_db['host'], $annotation_db['port']);

        // If SSL is enabled, set the SSL context options
        if ($annotation_db['ssl']) {
            $conn->setSslContextOptions([
                'verify_peer' => true
            ]);
        }

        // Create new Bolt instance and provide connection object
        $bolt = new \Bolt\Bolt($conn);

        // Build and get protocol version instance which creates connection and executes handshake
        $this->protocol = $bolt->build();
        
        // Login to database with credentials
        $this->protocol->hello(\Bolt\helpers\Auth::basic($annotation_db['username'], $annotation_db['password']));

    }

    abstract public function run($annotation_query, $params);

}