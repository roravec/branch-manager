<?php
require_once __DIR__ . '/../../interfaces/IWebApp.php';
class BranchManager implements IWebApp
{
    protected $rootApp;
    public function get($rootApplication): string
    {
        // Implementation for GET request
        return "GET request handled";
    }

    public function post($rootApplication): string
    {
        // Implementation for POST request
        return "POST request handled";
    }

    public function put($rootApplication): string
    {
        // Implementation for PUT request
        return "PUT request handled";
    }

    public function delete($rootApplication): string
    {
        // Implementation for DELETE request
        return "DELETE request handled";
    }

    public function methodNotAllowed(string $method, $rootApplication): string
    {
        // Implementation for handling unsupported methods
        return "Method $method not allowed";
    }
}

?>