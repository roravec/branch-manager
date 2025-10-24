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
            case 'branchspec':
                return $this->getBranchSpec($id);
            case 'branchspecs':
                return $this->getBranchSpecs();
            case 'log':
                return $this->getLogEntry($id);
            case 'logs':
                return $this->getLogEntries();
            case 'branchHasUser':
                return $this->getBranchHasUser($id);
            case 'branchHasUsers':
                return $this->getBranchUsers($id);
            case 'branchHasSpec':
                return $this->getBranchSpecsForBranch($id);
            case 'branchHasSpecs':
                return $this->getBranchSpecsList($id);
            default:
                return "Unknown GET endpoint.";
        }
    }

    public function post($rootApplication): string
    {
        $this->rootApp = $rootApplication;
        switch ($rootApplication->getUriSegment(0)) {
            case 'user':
                return $this->createUser();
            case 'branch':
                return $this->createBranch();
            case 'branchspec':
                return $this->createBranchSpec();
            case 'log':
                return $this->createLogEntry();
            case 'branchHasUser':
                return $this->assignBranchToUser();
            case 'branchHasSpec':
                return $this->assignBranchSpec();
            case 'login':
                return $this->login();
            case 'logout':
                return $this->logout();
            case 'authrefresh':
                return $this->authRefresh();
            case 'register':
                return $this->registerClient();
            default:
                return "Unknown POST endpoint.";
        }
    }

    public function put($rootApplication): string
    {
        $this->rootApp = $rootApplication;
        $id = $rootApplication->getUriSegment(1);
        switch ($rootApplication->getUriSegment(0)) {
            case 'user':
                return $this->editUser($id);
            case 'branch':
                return $this->editBranch($id);
            case 'branchspec':
                return $this->editBranchSpec($id);
            case 'log':
                return $this->editLogEntry($id);
            case 'branchHasUser':
                return $this->editBranchUserAssignment($id);
            case 'branchHasSpec':
                return $this->editBranchSpecAssignment($id);
            default:
                return "Unknown PUT endpoint.";
        }
    }

    public function delete($rootApplication): string
    {
        $this->rootApp = $rootApplication;
        $id = $rootApplication->getUriSegment(1);
        switch ($rootApplication->getUriSegment(0)) {
            case 'user':
                return $this->deleteUser($id);
            case 'branch':
                return $this->deleteBranch($id);
            case 'branchspec':
                return $this->deleteBranchSpec($id);
            case 'log':
                return $this->deleteLogEntry($id);
            case 'branchHasUser':
                return $this->deleteBranchUserAssignment($id);
            case 'branchHasSpec':
                return $this->deleteBranchSpecAssignment($id);
            default:
                return "Unknown DELETE endpoint.";
        }
    }

    public function methodNotAllowed(string $method, $rootApplication): string
    {
        // Implementation for handling unsupported methods
        return "Method $method not allowed";
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

    /* GET methods */
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
    public function getBranchSpec($id): string
    {
        return "getBranchSpec not implemented yet.";
    }
    public function getBranchSpecs(): string
    {
        return "getBranchSpecs not implemented yet.";
    }
    public function getLogEntry($id): string
    {
        return "getLogEntry not implemented yet.";
    }
    public function getLogEntries(): string
    {
        return "getLogEntries not implemented yet.";
    }
    public function getBranchHasUser($id): string
    {
        return "getBranchHasUser not implemented yet.";
    }
    public function getBranchUsers($id): string
    {
        return "getBranchUsers not implemented yet.";
    }
    public function getBranchSpecsForBranch($id): string
    {
        return "getBranchSpecsForBranch not implemented yet.";
    }
    public function getBranchSpecsList($id): string
    {
        return "getBranchSpecsList not implemented yet.";
    }

    /* POST methods */
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
    public function createBranch(): string
    {
        return "createBranch not implemented yet.";
    }
    public function createBranchSpec(): string
    {
        return "createBranchSpec not implemented yet.";
    }
    public function createLogEntry(): string
    {
        return "createLogEntry not implemented yet.";
    }
    public function assignBranchToUser(): string
    {
        return "assignBranchToUser not implemented yet.";
    }
    public function assignBranchSpec(): string
    {
        return "assignBranchSpec not implemented yet.";
    }


    /* PUT methods */
    public function editUser($id): string
    {
        return "editUser not implemented yet.";
    }

    public function editBranch($id): string
    {
        return "editBranch not implemented yet.";
    }

    public function editBranchSpec($id): string
    {
        return "editBranchSpec not implemented yet.";
    }

    public function editLogEntry($id): string
    {
        return "editLogEntry not implemented yet.";
    }

    public function editBranchUserAssignment($id): string
    {
        return "editBranchUserAssignment not implemented yet.";
    }

    public function editBranchSpecAssignment($id): string
    {
        return "editBranchSpecAssignment not implemented yet.";
    }

    /* DELETE methods */
    public function deleteUser($id): string
    {
        return "deleteUser not implemented yet.";
    }
    public function deleteBranch($id): string
    {
        return "deleteBranch not implemented yet.";
    }
    public function deleteBranchSpec($id): string
    {
        return "deleteBranchSpec not implemented yet.";
    }
    public function deleteLogEntry($id): string
    {
        return "deleteLogEntry not implemented yet.";
    }
    public function deleteBranchUserAssignment($id): string
    {
        return "deleteBranchUserAssignment not implemented yet.";
    }
    public function deleteBranchSpecAssignment($id): string
    {
        return "deleteBranchSpecAssignment not implemented yet.";
    }

}

?>