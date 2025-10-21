<?php
require_once __DIR__ . '/../../interfaces/IWebApp.php';
require_once __DIR__ . '/entities/Branch.php';

class BranchManager implements IWebApp
{
    protected $rootApp;
    public function get($rootApplication): string
    {
        $this->rootApp = $rootApplication;
        $id = $rootApplication->getUriSegment(1);
        switch ($rootApplication->getUriSegment(0))
        {
            case 'user':
                return $this->getUser($id);
            case 'users':
                return $this->getUsers();
            case 'branch':
                return $this->getBranch($id);
            case 'branches':
                return $this->getBranches();
            default:
                return "GET request handled";
        }
    }

    public function post($rootApplication): string
    {
        $this->rootApp = $rootApplication;
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
        $this->rootApp = $rootApplication;
        // Implementation for PUT request
        return "PUT request handled";
    }

    public function delete($rootApplication): string
    {
        $this->rootApp = $rootApplication;
        // Implementation for DELETE request
        return "DELETE request handled";
    }

    public function methodNotAllowed(string $method, $rootApplication): string
    {
        // Implementation for handling unsupported methods
        return "Method $method not allowed";
    }

    public function getBranch($id): string
    {
        $branch = new Branch($this->rootApp->getDatabase());
        $branch->read($id);
        return json_encode($branch);
    }
    public function getBranches(): string
    {
        $branches = Branch::readAll($this->rootApp->getDatabase());
        return json_encode($branches);
    }
    public function getUser($id): string
    {
        $client = new Client($this->rootApp->getDatabase());
        $client->read($id);
        return json_encode($client);
    }
    public function getUsers(): string
    {
        $clients = Client::readAll($this->rootApp->getDatabase());
        return json_encode($clients);
    }
}

?>