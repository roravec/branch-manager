<?php
require_once __DIR__ . '/../../interfaces/IWebApp.php';
require_once __DIR__ . '/entities/Branch.php';

class Rights {
    const NONE = 0;
    const VIEWER = 1;
    const EDITOR = 3;
    const MANAGER = 5;
    const ADMIN = 8;
    const SUPERADMIN = 10;
}
class BranchManager implements IWebApp
{
    protected $rootApp;
    function requireRights(int $requiredLevel, int $userLevel): void
    {
        if ($userLevel < $requiredLevel)
        {
            http_response_code(403);
            echo json_encode(['error' => 'Insufficient rights']);
            exit;
        }
    }
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
                return "Unknown GET endpoint.";
        }
    }

    public function post($rootApplication): string
    {
        $this->rootApp = $rootApplication;
        $identifier = $rootApplication->getUriSegment(1);
        switch ($rootApplication->getUriSegment(0)) {
            case 'user':
                if ($identifier === "create") {
                    return $this->createUser();
                }
                else if ($identifier === "register") {
                    return $this->registerClient();
                }
                else if ($identifier === "login") {
                    return $this->login();
                }
                else if ($identifier === "logout") {
                    return $this->logout();
                }
                else if ($identifier === "authrefresh") {
                    return $this->authRefresh();
                }
                break;
            default:
                return "Unknown POST endpoint.";
        }
        return "Unknown POST endpoint.";
    }

    public function put($rootApplication): string
    {
        $this->rootApp = $rootApplication;
        // Implementation for PUT request
        return "Unknown PUT endpoint.";
    }

    public function delete($rootApplication): string
    {
        $this->rootApp = $rootApplication;
        // Implementation for DELETE request
        return "Unknown DELETE endpoint.";
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
    public function createUser(): string
    {
        $this->requireRights(Rights::ADMIN, $this->rootApp->getClientAuth()->getClient()->getRights());
        // create a new client from payload json
        $client = new Client($this->rootApp->getDatabase());
        $client->identifier = isset($_POST['identifier']) ? $_POST['identifier'] : null;
        $client->name = isset($_POST['name']) ? $_POST['name'] : null;
        $client->secret_hash = isset($_POST['secret']) ? $_POST['secret'] : null;
        if (Client::readByIdentifier($this->rootApp->getDatabase(), $client->identifier))
        {
            http_response_code(400);
            return json_encode(['error' => 'Client with this identifier already exists']);
        }
        $client->create();
        $client->secret_hash = '';
        return json_encode($client);
    }

    public function registerClient(): string
    {
        $client = new Client($this->rootApp->getDatabase());
        $client->identifier = isset($_POST['identifier']) ? $_POST['identifier'] : null;
        $client->name = isset($_POST['name']) ? $_POST['name'] : null;
        $client->secret_hash = isset($_POST['secret']) ? $_POST['secret'] : null;
        if (Client::readByIdentifier($this->rootApp->getDatabase(), $client->identifier))
        {
            http_response_code(400);
            return json_encode(['error' => 'Client with this identifier already exists']);
        }
        $client->create();
        $client->secret_hash = '';
        return json_encode($client);
    }

    public function login(): string
    {
        $this->rootApp->getClientAuth()->login($_POST['identifier'], $_POST['secret'], isset($_POST['storeLogin']) ? $_POST['storeLogin'] : false);
        // send access token and refresh token in response
        if ($this->rootApp->getClientAuth()->isLoggedIn()) {
            return json_encode([
                'access_token' => $this->rootApp->getClientAuth()->getAccessToken(),
                'refresh_token' => $this->rootApp->getClientAuth()->getRefreshToken(),
                'expires_in' => 3600
            ]);
        }
        return json_encode(['error' => 'Login failed']);
    }

    public function logout(): string
    {
        $this->rootApp->getClientAuth()->logout();
        return json_encode(['message' => 'Logout successful']);
    }

    public function authRefresh(): string
    {
        $this->rootApp->getClientAuth()->refresh();
        if ($this->rootApp->getClientAuth()->isLoggedIn()) {
            return json_encode([
                'access_token' => $this->rootApp->getClientAuth()->getAccessToken(),
                'refresh_token' => $this->rootApp->getClientAuth()->getRefreshToken(),
                'expires_in' => 3600
            ]);
        }
        return json_encode(['error' => 'Login failed']);
    }
}

?>