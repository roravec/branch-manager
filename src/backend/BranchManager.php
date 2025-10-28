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
    function __construct()
    {
        header("Access-Control-Allow-Origin: https://branchmanager.erma.sk");
    }
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
                return $this->assignUserToBranch();
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
                'client_id' => $this->rootApp->getClientAuth()->getClient()->getId(),
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
                'client_id' => $this->rootApp->getClientAuth()->getClient()->getId(),
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
        $branchSpec = new BranchSpecialization($this->rootApp->getDatabase());
        $branchSpec->read($id);
        return json_encode($branchSpec);
    }
    public function getBranchSpecs(): string
    {
        $branchSpecs = BranchSpecialization::readAll($this->rootApp->getDatabase());
        return json_encode($branchSpecs);
    }
    public function getLogEntry($id): string
    {
        $logEntry = new LogEntry($this->rootApp->getDatabase());
        $logEntry->read($id);
        return json_encode($logEntry);
    }
    public function getLogEntries(): string
    {
        $logEntries = LogEntry::readAll($this->rootApp->getDatabase());
        return json_encode($logEntries);
    }
    public function getBranchHasUser($id): string
    {
        $branchHasUser = new BranchHasUser($this->rootApp->getDatabase());
        $branchHasUser->read($id);
        return json_encode($branchHasUser);
    }
    public function getBranchUsers($id): string
    {
        $branchUsers = BranchHasUser::readAll($this->rootApp->getDatabase(), "WHERE branchId = $id");
        return json_encode($branchUsers);
    }
    public function getBranchSpecsForBranch($id): string
    {
        $branchSpecs = BranchHasSpecialization::readAll($this->rootApp->getDatabase(), "WHERE branchId = $id");
        return json_encode($branchSpecs);
    }
    public function getBranchSpecsList($id): string
    {
        $branchSpecs = BranchHasSpecialization::readAll($this->rootApp->getDatabase(), "WHERE branchId = $id");
        return json_encode($branchSpecs);
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
        $this->requireRights(Rights::MANAGER, $this->rootApp->getClientAuth()->getClient()->getRights());
        // create a new branch from payload json
        $branch = new Branch($this->rootApp->getDatabase());
        $branch->name = isset($_POST['name']) ? $_POST['name'] : null;
        $branch->coordinates = isset($_POST['coordinates']) ? $_POST['coordinates'] : null;
        $branch->address = isset($_POST['address']) ? $_POST['address'] : null;
        $branch->address2 = isset($_POST['address2']) ? $_POST['address2'] : null;
        $branch->description = isset($_POST['description']) ? $_POST['description'] : null;
        $branch->employees = isset($_POST['employees']) ? $_POST['employees'] : null;
        $branch->utilization = isset($_POST['utilization']) ? $_POST['utilization'] : null;
        // check name is not empty
        if (empty($branch->name))
        {
            http_response_code(400);
            return json_encode(['error' => 'Branch name cannot be empty']);
        }
        $branch->create();
        return json_encode($branch);
    }
    public function createBranchSpec(): string
    {
        $this->requireRights(Rights::MANAGER, $this->rootApp->getClientAuth()->getClient()->getRights());
        // create a new branch specialization from payload json
        $branchSpec = new BranchSpecialization($this->rootApp->getDatabase());
        $branchSpec->name = isset($_POST['name']) ? $_POST['name'] : null;
        $branchSpec->description = isset($_POST['description']) ? $_POST['description'] : null;
        // check name is not empty
        if (empty($branchSpec->name))
        {
            http_response_code(400);
            return json_encode(['error' => 'Branch specialization name cannot be empty']);
        }
        $branchSpec->create();
        return json_encode($branchSpec);
    }
    public function createLogEntry(): string
    {
        $this->requireRights(Rights::EDITOR, $this->rootApp->getClientAuth()->getClient()->getRights());
        // create a new log entry from payload json
        $logEntry = new LogEntry($this->rootApp->getDatabase());
        $logEntry->branchId = isset($_POST['branchId']) ? $_POST['branchId'] : null;
        $logEntry->userId = isset($_POST['userId']) ? $_POST['userId'] : null;
        $logEntry->action = isset($_POST['action']) ? $_POST['action'] : null;
        $logEntry->timestamp = date('Y-m-d H:i:s');
        // check required fields are not empty
        if (empty($logEntry->branchId) || empty($logEntry->userId) || empty($logEntry->action) || empty($logEntry->timestamp))
        {
            http_response_code(400);
            return json_encode(['error' => 'Missing required fields for log entry']);
        }
        $logEntry->create();
        return json_encode($logEntry);
    }
    public function assignUserToBranch(): string
    {
        $this->requireRights(Rights::MANAGER, $this->rootApp->getClientAuth()->getClient()->getRights());
        // create a new branch-user assignment from payload json
        $branchUserAssignment = new BranchHasUser($this->rootApp->getDatabase());
        $branchUserAssignment->branchId = isset($_POST['branchId']) ? $_POST['branchId'] : null;
        $branchUserAssignment->userId = isset($_POST['userId']) ? $_POST['userId'] : null;
        // check required fields are not empty
        if (empty($branchUserAssignment->branchId) || empty($branchUserAssignment->userId))
        {
            http_response_code(400);
            return json_encode(['error' => 'Missing required fields for branch-user assignment']);
        }
        $branchUserAssignment->create();
        return json_encode($branchUserAssignment);
    }
    public function assignBranchSpec(): string
    {
        $this->requireRights(Rights::MANAGER, $this->rootApp->getClientAuth()->getClient()->getRights());
        // create a new branch-specialization assignment from payload json
        $branchSpecAssignment = new BranchHasSpecialization($this->rootApp->getDatabase());
        $branchSpecAssignment->branchId = isset($_POST['branchId']) ? $_POST['branchId'] : null;
        $branchSpecAssignment->specId = isset($_POST['specId']) ? $_POST['specId'] : null;
        // check required fields are not empty
        if (empty($branchSpecAssignment->branchId) || empty($branchSpecAssignment->specId))
        {
            http_response_code(400);
            return json_encode(['error' => 'Missing required fields for branch-specialization assignment']);
        }
        $branchSpecAssignment->create();
        return json_encode($branchSpecAssignment);
    }


    /* PUT methods */
    public function editUser($id): string
    {
        $this->requireRights(Rights::ADMIN, $this->rootApp->getClientAuth()->getClient()->getRights());
        // edit user by id
        $user = new Client($this->rootApp->getDatabase());
        $user->read($id);
        if (!$user->exists()) {
            http_response_code(404);
            return json_encode(['error' => 'User not found']);
        }

        $user->name = isset($_POST['name']) ? $_POST['name'] : $user->name;
        $user->identifier = isset($_POST['identifier']) ? $_POST['identifier'] : $user->identifier;
        $user->secret_hash = isset($_POST['secret_hash']) ? $_POST['secret_hash'] : $user->secret_hash;
        $user->rights = isset($_POST['rights']) ? intval($_POST['rights']) : $user->rights;
        $user->status = isset($_POST['status']) ? intval($_POST['status']) : $user->status;
        $user->type = isset($_POST['type']) ? intval($_POST['type']) : $user->type;
        $user->update();
        return json_encode($user);
    }

    public function editBranch($id): string
    {
        $this->requireRights(Rights::MANAGER, $this->rootApp->getClientAuth()->getClient()->getRights());
        // edit branch by id
        $branch = new Branch($this->rootApp->getDatabase());
        $branch->read($id);
        if (!$branch->exists()) {
            http_response_code(404);
            return json_encode(['error' => 'Branch not found']);
        }
        $branch->name = isset($_POST['name']) ? $_POST['name'] : $branch->name;
        $branch->coordinates = isset($_POST['coordinates']) ? $_POST['coordinates'] : $branch->coordinates;
        $branch->address = isset($_POST['address']) ? $_POST['address'] : $branch->address;
        $branch->address2 = isset($_POST['address2']) ? $_POST['address2'] : $branch->address2;
        $branch->description = isset($_POST['description']) ? $_POST['description'] : $branch->description;
        $branch->employees = isset($_POST['employees']) ? intval($_POST['employees']) : $branch->employees;
        $branch->utilization = isset($_POST['utilization']) ? intval($_POST['utilization']) : $branch->utilization;

        $branch->update();
        return json_encode($branch);
    }
    public function editBranchSpec($id): string
    {
        $this->requireRights(Rights::MANAGER, $this->rootApp->getClientAuth()->getClient()->getRights());
        // edit branch specialization by id
        $branchSpec = new BranchSpecialization($this->rootApp->getDatabase());
        $branchSpec->read($id);
        if (!$branchSpec->exists()) {
            http_response_code(404);
            return json_encode(['error' => 'Branch specialization not found']);
        }
        $branchSpec->name = isset($_POST['name']) ? $_POST['name'] : $branchSpec->name;
        $branchSpec->description = isset($_POST['description']) ? $_POST['description'] : $branchSpec->description;
        $branchSpec->update();
        return json_encode($branchSpec);
    }

    public function editLogEntry($id): string
    {
        $this->requireRights(Rights::EDITOR, $this->rootApp->getClientAuth()->getClient()->getRights());
        // edit log entry by id
        $logEntry = new LogEntry($this->rootApp->getDatabase());
        $logEntry->read($id);
        if (!$logEntry->exists()) {
            http_response_code(404);
            return json_encode(['error' => 'Log entry not found']);
        }
        $logEntry->message = isset($_POST['message']) ? $_POST['message'] : $logEntry->message;
        $logEntry->update();
        return json_encode($logEntry);
    }

    public function editBranchUserAssignment($id): string
    {
        $this->requireRights(Rights::MANAGER, $this->rootApp->getClientAuth()->getClient()->getRights());
        // edit branch-user assignment by id
        $branchUserAssignment = new BranchHasUser($this->rootApp->getDatabase());
        $branchUserAssignment->read($id);
        if (!$branchUserAssignment->exists()) {
            http_response_code(404);
            return json_encode(['error' => 'Branch-user assignment not found']);
        }
        $branchUserAssignment->branchId = isset($_POST['branchId']) ? $_POST['branchId'] : $branchUserAssignment->branchId;
        $branchUserAssignment->userId = isset($_POST['userId']) ? $_POST['userId'] : $branchUserAssignment->userId;
        $branchUserAssignment->userRights = isset($_POST['userRights']) ? $_POST['userRights'] : $branchUserAssignment->userRights;

        $branchUserAssignment->update();
        return json_encode($branchUserAssignment);
    }
    public function editBranchSpecAssignment($id): string
    {
        $this->requireRights(Rights::MANAGER, $this->rootApp->getClientAuth()->getClient()->getRights());
        // edit branch-specialization assignment by id
        $branchSpecAssignment = new BranchHasSpecialization($this->rootApp->getDatabase());
        $branchSpecAssignment->read($id);
        if (!$branchSpecAssignment->exists()) {
            http_response_code(404);
            return json_encode(['error' => 'Branch-specialization assignment not found']);
        }
        $branchSpecAssignment->branchId = isset($_POST['branchId']) ? $_POST['branchId'] : $branchSpecAssignment->branchId;
        $branchSpecAssignment->branchSpecializationId = isset($_POST['branchSpecializationId']) ? $_POST['branchSpecializationId'] : $branchSpecAssignment->branchSpecializationId;
        $branchSpecAssignment->update();
        return json_encode($branchSpecAssignment);
    }

    /* DELETE methods */
    public function deleteUser($id): string
    {
        $this->requireRights(Rights::ADMIN, $this->rootApp->getClientAuth()->getClient()->getRights());
        // delete user by id
        $user = new Client($this->rootApp->getDatabase());
        $user->delete($id);
        return json_encode(['message' => 'User deleted successfully']);
    }
    public function deleteBranch($id): string
    {
        $this->requireRights(Rights::ADMIN, $this->rootApp->getClientAuth()->getClient()->getRights());
        // delete branch by id
        $branch = new Branch($this->rootApp->getDatabase());
        $branch->delete($id);
        return json_encode(['message' => 'Branch deleted successfully']);
    }
    public function deleteBranchSpec($id): string
    {
        $this->requireRights(Rights::ADMIN, $this->rootApp->getClientAuth()->getClient()->getRights());
        // delete branch-specialization assignment by id
        $branchSpec = new BranchHasSpecialization($this->rootApp->getDatabase());
        $branchSpec->delete($id);
        return json_encode(['message' => 'Branch-specialization assignment deleted successfully']);
    }
    public function deleteLogEntry($id): string
    {
        $this->requireRights(Rights::EDITOR, $this->rootApp->getClientAuth()->getClient()->getRights());
        // delete log entry by id
        $logEntry = new LogEntry($this->rootApp->getDatabase());
        $logEntry->delete($id);
        return json_encode(['message' => 'Log entry deleted successfully']);
    }
    public function deleteBranchUserAssignment($id): string
    {
        $this->requireRights(Rights::ADMIN, $this->rootApp->getClientAuth()->getClient()->getRights());
        // delete branch-user assignment by id
        $branchUserAssignment = new BranchHasUser($this->rootApp->getDatabase());
        $branchUserAssignment->delete($id);
        return json_encode(['message' => 'Branch-user assignment deleted successfully']);
    }
    public function deleteBranchSpecAssignment($id): string
    {
        $this->requireRights(Rights::ADMIN, $this->rootApp->getClientAuth()->getClient()->getRights());
        // delete branch-specialization assignment by id
        $branchSpecAssignment = new BranchHasSpecialization($this->rootApp->getDatabase());
        $branchSpecAssignment->delete($id);
        return json_encode(['message' => 'Branch-specialization assignment deleted successfully']);
    }

}

?>