<?php
/**
 * Supabase PHP Helper Class
 * Simple wrapper for Supabase REST API
 */

class Supabase {
    private $url;
    private $key;
    private $table;
    
    /**
     * Initialize Supabase connection
     * 
     * @param string $table The table name to query
     */
    public function __construct($table) {
        $this->url = $_ENV['SUPABASE_URL'] ?? getenv('SUPABASE_URL');
        $this->key = $_ENV['SUPABASE_ANON_KEY'] ?? getenv('SUPABASE_ANON_KEY');
        $this->table = $table;
        
        if (!$this->url || !$this->key) {
            throw new Exception('Missing Supabase credentials in environment variables');
        }
    }
    
    /**
     * Make HTTP request to Supabase API
     * 
     * @param string $method HTTP method (GET, POST, PATCH, DELETE)
     * @param string $query URL query string for filtering
     * @param array $data Data to send in request body
     * @return array Response from Supabase
     */
    private function request($method, $query = '', $data = null) {
        $url = $this->url . "/rest/v1/{$this->table}";
        if ($query) {
            $url .= "?" . $query;
        }
        
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . $this->key,
            'Content-Type: application/json',
            'Prefer: return=representation'
        ]);
        
        if ($data) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        }
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);
        
        if ($error) {
            throw new Exception("CURL Error: $error");
        }
        
        if ($httpCode >= 400) {
            throw new Exception("Supabase API Error (HTTP $httpCode): $response");
        }
        
        return json_decode($response, true);
    }
    
    /**
     * Select records from table
     * 
     * @param string $filters Optional filter string (e.g., "username=eq.john&status=eq.active")
     * @return array Array of records
     */
    public function select($filters = '') {
        $query = 'order=id.asc';
        if ($filters) {
            $query = $filters;
        }
        return $this->request('GET', $query) ?? [];
    }
    
    /**
     * Insert a record
     * 
     * @param array $data Record data to insert
     * @return array Inserted record with ID
     */
    public function insert($data) {
        return $this->request('POST', '', $data);
    }
    
    /**
     * Update a record by ID
     * 
     * @param int $id Record ID
     * @param array $data Data to update
     * @return array Updated record
     */
    public function update($id, $data) {
        return $this->request('PATCH', "id=eq.$id", $data);
    }
    
    /**
     * Delete a record by ID
     * 
     * @param int $id Record ID
     */
    public function delete($id) {
        $this->request('DELETE', "id=eq.$id");
    }
    
    /**
     * Query records with custom filter string
     * 
     * @param string $filter Filter string (e.g., "username=eq.john")
     * @return array Array of matching records
     */
    public function query($filter) {
        return $this->request('GET', $filter) ?? [];
    }
    
    /**
     * Find record by ID
     * 
     * @param int $id Record ID
     * @return array Single record or null
     */
    public function findById($id) {
        $results = $this->query("id=eq.$id");
        return isset($results[0]) ? $results[0] : null;
    }
    
    /**
     * Count records matching filter
     * 
     * @param string $filter Optional filter
     * @return int Count of records
     */
    public function count($filter = '') {
        $url = $this->url . "/rest/v1/{$this->table}";
        if ($filter) {
            $url .= "?" . $filter;
        }
        $url .= (strpos($url, '?') ? '&' : '?') . 'select=count()';
        
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . $this->key,
            'Prefer: count=exact'
        ]);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($httpCode >= 400) {
            throw new Exception("Supabase API Error: $response");
        }
        
        return (int)curl_getinfo($ch, CURLINFO_HEADER_OUT);
    }
}
?>
