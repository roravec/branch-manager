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
        $id = $rootApplication->getUriSegment(1);
        switch ($rootApplication->getUriSegment(0)) {
            case 'user':
                return "POST user: $id";
            case 'users':
                return "POST users: $id";
            default:
                return "POST request handled";
        }
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