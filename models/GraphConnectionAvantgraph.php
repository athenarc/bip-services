<?php

namespace app\models;

use Yii;
use yii\helpers\Url;

class GraphConnectionAvantgraph extends GraphConnection {
    
    public function run($annotation_query, $params) {

        $query = self::prepareQuery($annotation_query, $params);

        // Execute query with parameters
        $stats = $this->protocol->run($query);
        $fields = $stats['fields'];

        // Pull records from last executed query
        $rows = $this->protocol->pull();

        // remove last row (neo4j miscellaneous data)
        $rows = array_slice($rows, 0, -1);

        // TODO: this is a temp solution until Avantgraph supports COLLECT statement; 
        // we should find a way to by-pass if not needed
        $rows = self::postProcess($rows, $fields);

        return [
            $stats, 
            $rows
        ];

    }


    // this function replaces params within the annotation query since Avantgraph does not support query with parameters
    // TODO: remove if this feature in the future by Avantgraph...
    private function prepareQuery($annotation_query, $params) {

        // Loop through each parameter and replace it in the query
        foreach ($params as $key => $value) {
            // Define the placeholder with a colon prefix (e.g., ":key")
            $placeholder = '$' . $key;
                        
            // Escape and safely quote values to prevent SQL injection
            $escapedValue = self::escapeValue($value);

            // Replace the placeholder in the query
            $annotation_query = str_replace($placeholder, $escapedValue, $annotation_query);
        }

        return $annotation_query;
    }

    private function escapeValue($value) {

         // If the value is an array, encode it as JSON
        if (is_array($value)) {
            return json_encode($value, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        }

        // Escape string values with quotes for safe SQL insertion
        if (is_string($value)) {
            return "'" . addslashes($value) . "'";
        } elseif (is_numeric($value)) {
            return $value;
        } else {
            throw new InvalidArgumentException('Unsupported parameter type');
        }
    }

    public function postProcess($data, $fields) {

        // Initialize the result array
        $result = [];

        // Process each row in the data array
        foreach ($data as $row) {
            $doi = $row[0]; // The first item is the DOI
            
            // Prepare the `data` array for this DOI entry
            $dataEntry = [];
            foreach ($fields as $index => $field) {
                if ($index > 0) { // Skip the first field, which is `doi`
                    $dataEntry[] = [
                        'label' => $field,
                        'value' => preg_replace('/\[.*?\]/', '', $row[$index]) // escapes [url: http_link] that is present in many descriptions in CKG -- remove if needed
                    ]; 
                }
            }
            
            // Build the structured array format
            $result[$doi][] = [
                'label' => $row[1], // `name` field, for example: "spinal disease"
                'data' => $dataEntry
            ];
        }

        // Convert the result to a structure where DOI is the main index
        $output = [];
        foreach ($result as $doi => $entries) {
            $output[] = [
                $doi,  // DOI as the first element
                $entries // Array of grouped data
            ];
        }

        return $output;
    }

}