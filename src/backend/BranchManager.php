<?php
require_once __DIR__ . '/../../interfaces/IWebApp.php';
require_once __DIR__ . '/entities/Branch.php';
require_once __DIR__ . '/entities/BranchHasUser.php';
require_once __DIR__ . '/entities/BranchHasSpecialization.php';
require_once __DIR__ . '/entities/BranchSpecialization.php';

class UserRights {
    const NONE = 0;
    const VIEWER = 1;
    const BRANCHMANAGER = 2;
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
        if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS')
        {
            header('Access-Control-Allow-Origin: *');
            header('Access-Control-Allow-Methods: GET, POST, DELETE, OPTIONS');
            header('Access-Control-Allow-Headers: Content-Type, Authorization, x-refresh-token');
            header('Access-Control-Allow-Credentials: true');
            http_response_code(200);
            exit;
        }
        // header("Access-Control-Allow-Origin: https://branchmanager.erma.sk");
        header("Access-Control-Allow-Origin: *");
    }
    /**
     * Checks if the user has the required rights.
     * @param int $requiredLevel
     * @param int $userLevel
     * @return void
     */
    function requireRights(int $requiredLevel, int $userLevel): void
    {
        if ($userLevel < $requiredLevel)
        {
            http_response_code(403);
            echo json_encode(['error' => "Insufficient rights."]);
            exit;
        }
    }

    /**
     * Adds a log entry to the database.
     * @param string $action
     * @param string $targetType
     * @param int $targetId
     * @param int $status
     * @param string $message
     * @return void
     */
    public function addLogEntry(string $action, string $targetType, int $targetId, int $status, string $message): void
    {
        $logEntry = new LogEntry($this->rootApp->getDatabase());
        $logEntry->timestamp = date('Y-m-d H:i:s');
        $logEntry->userId = $this->rootApp->getClientAuth()->getClient()->getId();
        $logEntry->clientIp = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        $logEntry->action = $action;
        $logEntry->targetType = $targetType;
        $logEntry->targetId = $targetId;
        $logEntry->status = $status;
        $logEntry->message = $message;
        $logEntry->create();
    }

    /**
     * Handles GET requests.
     * @param mixed $rootApplication
     * @return string
     */
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
            case 'managedBranches':
                return $this->getManagedBranches();
            case 'myBranches':
                return $this->getMyBranches();
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

    /**
     * Handles POST requests.
     * @param mixed $rootApplication
     * @return string
     */
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
            case 'edit':
                return $this->putPost($rootApplication);
            default:
                return "Unknown POST endpoint.";
        }
    }

    /**
     * Handles PUT/ POST requests.
     * @param mixed $rootApplication
     * @return string
     */
    public function putPost($rootApplication): string
    {
        $this->rootApp = $rootApplication;
        $id = $rootApplication->getUriSegment(2);
        switch ($rootApplication->getUriSegment(1)) {
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

    /**
     * Handles PUT requests. Doesnt't work due to webhosting limitations.
     * @param mixed $rootApplication
     * @return string
     */
    public function put($rootApplication): string
    {
        return '';
    }

    /**
     * Handles DELETE requests.
     * @param mixed $rootApplication
     * @return string
     */
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

    /**
     * Handles unsupported methods.
     * @param string $method
     * @param mixed $rootApplication
     * @return string
     */
    public function methodNotAllowed(string $method, $rootApplication): string
    {
        // Implementation for handling unsupported methods
        return "Method $method not allowed";
    }

    function isBranchManager($branchId): bool
    {
        $branchHasUser = BranchHasUser::readAll($this->rootApp->getDatabase(), "WHERE branchId = $branchId AND userId = " . $this->rootApp->getClientAuth()->getClient()->getId() . " AND userRights >= " . UserRights::BRANCHMANAGER);
        if (count($branchHasUser) == 0)
        {
            // user is not a branch manager for this branch
            return false;
        }
        else
        {
            // user is a branch manager for this branch
            return true;
        }
    }

    function getUserRights($branchId = 0)
    {
        $userRights = $this->rootApp->getClientAuth()->getClient()->getRights();
        $branchManagerRights = 0;
        if ($branchId != 0)
        {
            $branchHasUser = BranchHasUser::readAll($this->rootApp->getDatabase(), "WHERE branchId = $branchId AND userId = " . $this->rootApp->getClientAuth()->getClient()->getId());
            if (count($branchHasUser) > 0)
            {
                $branchManagerRights = $branchHasUser[0]->userRights;
            }
        }
        return max($userRights, $branchManagerRights);
    }

    /**
     * Registers a new client.
     * @return string
     */
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
        $client->id = $this->rootApp->getDatabase()->lastInsertID();
        $this->addLogEntry('register', 'client', $client->id, 1, 'New client registered: ' . $client->identifier);
        return json_encode($client);
    }

    /**
     * Handles client login.
     * @return string
     */
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
        $this->addLogEntry('login', 'client', 0, 0, 'Failed login attempt for identifier: ' . $_POST['identifier']);
        return json_encode(['error' => 'Login failed']);
    }

    /**
     * Handles client logout.
     * @return string
     */
    public function logout(): string
    {
        $this->rootApp->getClientAuth()->logout();
        return json_encode(['message' => 'Logout successful']);
    }

    /**
     * Handles token refresh.
     * @return string
     */
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
        $this->addLogEntry('auth_refresh', 'client', 0, 0, 'Failed token refresh attempt.');
        return json_encode(['error' => 'Login failed']);
    }

    /* GET methods *****************************************************/

    /**
     * Returns branch details as JSON.
     * @param int $id
     * @return string
     */
    public function getBranch($id): string
    {
        $branch = new Branch($this->rootApp->getDatabase());
        $branch->read($id);
        return json_encode($branch);
    }
    /**
     * Returns all branches as JSON.
     * @return string
     */
    public function getBranches(): string
    {
        $branches = Branch::readAll($this->rootApp->getDatabase());
        return json_encode($branches);
    }
    /**
     * Returns all branches where user is manager as JSON.
     * @return string
     */
    public function getManagedBranches(): string
    {
        $userId = $this->rootApp->getClientAuth()->getClient()->getId();
        $branchUsers = BranchHasUser::readAll($this->rootApp->getDatabase(), "WHERE userId = $userId AND userRights >= " . UserRights::BRANCHMANAGER);
        $branches = [];
        foreach ($branchUsers as $branchUser)
        {
            $branch = new Branch($this->rootApp->getDatabase());
            $branch->read($branchUser->branchId);
            $branches[] = $branch;
        }
        return json_encode($branches);
    }

    /**
     * Returns all branches where user is assigned as JSON.
     * @return string
     */
    public function getMyBranches(): string
    {
        $userId = $this->rootApp->getClientAuth()->getClient()->getId();
        $branchUsers = BranchHasUser::readAll($this->rootApp->getDatabase(), "WHERE userId = $userId");
        $branches = [];
        foreach ($branchUsers as $branchUser)
        {
            $branch = new Branch($this->rootApp->getDatabase());
            $branch->read($branchUser->branchId);
            $branches[] = $branch;
        }
        return json_encode($branches);
    }

    /**
     * Returns user details as JSON.
     * @param int $id
     * @return string
     */
    public function getUser($id): string
    {
        $client = new Client($this->rootApp->getDatabase());
        $client->read($id);
        return json_encode($client);
    }
    /**
     * Returns all users as JSON.
     * @return string
     */
    public function getUsers(): string
    {
        $clients = Client::readAll($this->rootApp->getDatabase());
        return json_encode($clients);
    }
    /**
     * Returns branch specialization details as JSON.
     * @param int $id
     * @return string
     */
    public function getBranchSpec($id): string
    {
        $branchSpec = new BranchSpecialization($this->rootApp->getDatabase());
        $branchSpec->read($id);
        return json_encode($branchSpec);
    }
    /**
     * Returns all branch specializations as JSON.
     * @return string
     */
    public function getBranchSpecs(): string
    {
        $branchSpecs = BranchSpecialization::readAll($this->rootApp->getDatabase());
        return json_encode($branchSpecs);
    }
    /**
     * Returns log entry details as JSON.
     * @param int $id
     * @return string
     */
    public function getLogEntry($id): string
    {
        $logEntry = new LogEntry($this->rootApp->getDatabase());
        $logEntry->read($id);
        return json_encode($logEntry);
    }
    /**
     * Returns all log entries as JSON.
     * @return string
     */
    public function getLogEntries(): string
    {
        $logEntries = LogEntry::readAll($this->rootApp->getDatabase());
        return json_encode($logEntries);
    }
    /**
     * Returns branch-user relationship details as JSON.
     * @param int $id
     * @return string
     */
    public function getBranchHasUser($id): string
    {
        if ($id == 0)
        {
            // return error
            http_response_code(400);
            return json_encode(['error' => 'Invalid branch ID']);
        }
        $branchHasUser = new BranchHasUser($this->rootApp->getDatabase());
        $branchHasUser->read($id);
        return json_encode($branchHasUser);
    }
    /**
     * Returns all users for a branch as JSON.
     * @param int $id
     * @return string
     */
    public function getBranchUsers($id): string
    {
        if ($id == 0)
        {
            // return error
            http_response_code(400);
            return json_encode(['error' => 'Invalid branch ID']);
        }
        $branchUsers = BranchHasUser::readAll($this->rootApp->getDatabase(), "WHERE branchId = $id");
        return json_encode($branchUsers);
    }
    /**
     * Returns all branch specializations for a branch as JSON.
     * @param int $id
     * @return string
     */
    public function getBranchSpecsForBranch($id): string
    {
        if ($id == 0)
        {
            // return error
            http_response_code(400);
            return json_encode(['error' => 'Invalid branch ID']);
        }
        $branchSpecs = BranchHasSpecialization::readAll($this->rootApp->getDatabase(), "WHERE branchId = $id");
        return json_encode($branchSpecs);
    }
    /**
     * Returns a list of branch specializations for a branch as JSON.
     * @param int $id
     * @return string
     */
    public function getBranchSpecsList($id): string
    {
        if ($id == 0)
        {
            // return error
            http_response_code(400);
            return json_encode(['error' => 'Invalid branch ID']);
        }
        $branchSpecs = BranchHasSpecialization::readAll($this->rootApp->getDatabase(), "WHERE branchId = $id");
        return json_encode($branchSpecs);
    }

    /* POST methods ***************************************************/

    /**
     * Creates a new user from POST data.
     * @return string
     */
    public function createUser(): string
    {
        $this->requireRights(UserRights::ADMIN, $this->getUserRights());
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
        $this->addLogEntry('create', 'client', $client->id, 1, 'New client created: ' . $client->identifier);
        return json_encode($client);
    }
    /**
     * Creates a new branch from POST data.
     * @return string
     */
    public function createBranch(): string
    {
        $this->requireRights(UserRights::MANAGER, $this->getUserRights());
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
        $this->addLogEntry('create', 'branch', $branch->id, 1, 'New branch created: ' . $branch->name);
        return json_encode($branch);
    }
    /**
     * Creates a new branch specialization from POST data.
     * @return string
     */
    public function createBranchSpec(): string
    {
        $this->requireRights(UserRights::MANAGER, $this->getUserRights());
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
        $this->addLogEntry('create', 'branchSpecialization', $branchSpec->id, 1, 'New branch specialization created: ' . $branchSpec->name);
        return json_encode($branchSpec);
    }
    /**
     * Creates a new log entry from POST data.
     * @return string
     */
    public function createLogEntry(): string
    {
        $this->requireRights(UserRights::BRANCHMANAGER, $this->getUserRights());
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
    /**
     * Creates a new branch-user assignment from POST data.
     * @return string
     */
    public function assignUserToBranch(): string
    {
        // create a new branch-user assignment from payload json
        $branchUserAssignment = new BranchHasUser($this->rootApp->getDatabase());
        $branchUserAssignment->branchId = isset($_POST['branchId']) ? $_POST['branchId'] : null;
        $branchUserAssignment->userId = isset($_POST['userId']) ? $_POST['userId'] : null;
        $this->requireRights(UserRights::BRANCHMANAGER, $this->getUserRights($branchUserAssignment->branchId));
        // check required fields are not empty
        if (empty($branchUserAssignment->branchId) || empty($branchUserAssignment->userId))
        {
            http_response_code(400);
            return json_encode(['error' => 'Missing required fields for branch-user assignment']);
        }
        $branchUserAssignment->create();
        $this->addLogEntry('create', 'branchUserAssignment', $branchUserAssignment->id, 1, 'New branch-user assignment created: Branch ID ' . $branchUserAssignment->branchId . ', User ID ' . $branchUserAssignment->userId);
        return json_encode($branchUserAssignment);
    }
    /**
     * Creates a new branch-specialization assignment from POST data.
     * @return string
     */
    public function assignBranchSpec(): string
    {
        // create a new branch-specialization assignment from payload json
        $branchSpecAssignment = new BranchHasSpecialization($this->rootApp->getDatabase());
        $branchSpecAssignment->branchId = isset($_POST['branchId']) ? $_POST['branchId'] : null;
        $branchSpecAssignment->branchSpecializationId = isset($_POST['specId']) ? $_POST['specId'] : null;
        $this->requireRights(UserRights::BRANCHMANAGER, $this->getUserRights($branchSpecAssignment->branchId));
        // check required fields are not empty
        if (empty($branchSpecAssignment->branchId) || empty($branchSpecAssignment->branchSpecializationId))
        {
            http_response_code(400);
            return json_encode(['error' => 'Missing required fields for branch-specialization assignment']);
        }
        $branchSpecAssignment->create();
        $this->addLogEntry('create', 'branchSpecAssignment', $branchSpecAssignment->id, 1, 'New branch-specialization assignment created: Branch ID ' . $branchSpecAssignment->branchId . ', Spec ID ' . $branchSpecAssignment->branchSpecializationId);
        return json_encode($branchSpecAssignment);
    }


    /* PUT methods ***************************************************/

    /**
     * Edits an existing user by ID from POST data.
     * @param int $id
     * @return string
     */
    public function editUser($id): string
    {
        //return json_encode(['message' => $this->rootApp->getClientAuth()->getClient()->getIdentifier() . " is logged in: " . ($this->rootApp->getClientAuth()->isLoggedIn() ? 'yes' : 'no')]);

        // find out if current logged user manages any branch
        $branchHasUser = BranchHasUser::readAll($this->rootApp->getDatabase(), "WHERE userId = " . $this->rootApp->getClientAuth()->getClient()->getId() . " AND userRights >= " . UserRights::BRANCHMANAGER);
        if (count($branchHasUser) == 0)
        {
            // user is not a branch manager for this branch
            $this->requireRights(UserRights::ADMIN, $this->getUserRights());
        }
        else
        {
            // user is a branch manager for this branch
            $this->requireRights(UserRights::BRANCHMANAGER, $this->getUserRights($branchHasUser[0]->branchId));
        }
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
        $this->addLogEntry('edit', 'client', $user->id, 1, 'User edited: ' . $user->identifier);
        return json_encode($user);
    }

    /**
     * Edits an existing branch by ID from POST data.
     * @param int $id
     * @return string
     */
    public function editBranch($id): string
    {
        $this->requireRights(UserRights::BRANCHMANAGER, $this->getUserRights($id));
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
        $this->addLogEntry('edit', 'branch', $branch->id, 1, 'Branch edited: ' . $branch->name);
        return json_encode($branch);
    }
    /**
     * Edits an existing branch specialization by ID from POST data.
     * @param int $id
     * @return string
     */
    public function editBranchSpec($id): string
    {
        $this->requireRights(UserRights::MANAGER, $this->getUserRights());
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
        $this->addLogEntry('edit', 'branchSpecialization', $branchSpec->id, 1, 'Branch specialization edited: ' . $branchSpec->name);
        return json_encode($branchSpec);
    }

    /**
     * Edits an existing log entry by ID from POST data.
     * @param int $id
     * @return string
     */
    public function editLogEntry($id): string
    {
        $this->requireRights(UserRights::ADMIN, $this->getUserRights());
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

    /**
     * Edits an existing branch-user assignment by ID from POST data.
     * @param int $id
     * @return string
     */
    public function editBranchUserAssignment($id): string
    {
        // edit branch-user assignment by id
        $branchUserAssignment = new BranchHasUser($this->rootApp->getDatabase());
        $branchUserAssignment->read($id);
        if (!$branchUserAssignment->exists()) {
            http_response_code(404);
            return json_encode(['error' => 'Branch-user assignment not found']);
        }
        $branchUserAssignment->branchId = isset($_POST['branchId']) ? $_POST['branchId'] : $branchUserAssignment->branchId;
        $this->requireRights(UserRights::BRANCHMANAGER, $this->getUserRights($branchUserAssignment->branchId));
        $branchUserAssignment->userId = isset($_POST['userId']) ? $_POST['userId'] : $branchUserAssignment->userId;
        $branchUserAssignment->userRights = isset($_POST['userRights']) ? $_POST['userRights'] : $branchUserAssignment->userRights;

        $branchUserAssignment->update();
        $this->addLogEntry('edit', 'branchUserAssignment', $branchUserAssignment->id, 1, 'Branch-user assignment edited: Branch ID ' . $branchUserAssignment->branchId . ', User ID ' . $branchUserAssignment->userId);
        return json_encode($branchUserAssignment);
    }
    /**
     * Edits an existing branch-specialization assignment by ID from POST data.
     * @param int $id
     * @return string
     */
    public function editBranchSpecAssignment($id): string
    {
        $this->requireRights(UserRights::EDITOR, $this->getUserRights());
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
        $this->addLogEntry('edit', 'branchSpecAssignment', $branchSpecAssignment->id, 1, 'Branch-specialization assignment edited: Branch ID ' . $branchSpecAssignment->branchId . ', Spec ID ' . $branchSpecAssignment->branchSpecializationId);
        return json_encode($branchSpecAssignment);
    }

    /* DELETE methods ***************************************************/

    /**
     * Deletes an existing user by ID.
     * @param int $id
     * @return string
     */
    public function deleteUser($id): string
    {
        $this->requireRights(UserRights::ADMIN, $this->getUserRights());
        // delete user by id
        $user = new Client($this->rootApp->getDatabase());
        $user->delete($id);
        $this->addLogEntry('delete', 'client', $id, 1, 'User deleted: ID ' . $id);
        return json_encode(['message' => 'User deleted successfully']);
    }
    /**
     * Deletes an existing branch by ID.
     * @param int $id
     * @return string
     */
    public function deleteBranch($id): string
    {
        $this->requireRights(UserRights::ADMIN, $this->getUserRights());
        // delete branch by id
        $branch = new Branch($this->rootApp->getDatabase());
        $branch->delete($id);
        $this->addLogEntry('delete', 'branch', $id, 1, 'Branch deleted: ID ' . $id);
        return json_encode(['message' => 'Branch deleted successfully']);
    }
    /**
     * Deletes an existing branch specialization by ID.
     * @param int $id
     * @return string
     */
    public function deleteBranchSpec($id): string
    {
        $this->requireRights(UserRights::EDITOR, $this->getUserRights());
        // delete branch-specialization assignment by id
        $branchSpec = new BranchSpecialization($this->rootApp->getDatabase());
        $branchSpec->delete($id);
        $this->addLogEntry('delete', 'branchSpecialization', $id, 1, 'Branch specialization deleted: ID ' . $id);
        return json_encode(['message' => 'Branch-specialization assignment deleted successfully']);
    }
    /**
     * Deletes an existing log entry by ID.
     * @param int $id
     * @return string
     */
    public function deleteLogEntry($id): string
    {
        $this->requireRights(UserRights::ADMIN, $this->getUserRights());
        // delete log entry by id
        $logEntry = new LogEntry($this->rootApp->getDatabase());
        $logEntry->delete($id);
        return json_encode(['message' => 'Log entry deleted successfully']);
    }
    /**
     * Deletes an existing branch-user assignment by ID.
     * @param int $id
     * @return string
     */
    public function deleteBranchUserAssignment($id): string
    {
        // delete branch-user assignment by id
        $branchUserAssignment = new BranchHasUser($this->rootApp->getDatabase());
        $branchUserAssignment->read($id);
        $this->requireRights(UserRights::BRANCHMANAGER, $this->getUserRights($branchUserAssignment->branchId));
        $branchUserAssignment->delete($id);
        $this->addLogEntry('delete', 'branchUserAssignment', $id, 1, 'Branch-user assignment deleted: ID ' . $id);
        return json_encode(['message' => 'Branch-user assignment deleted successfully']);
    }
    /**
     * Deletes an existing branch-specialization assignment by ID.
     * @param int $id
     * @return string
     */
    public function deleteBranchSpecAssignment($id): string
    {
        $this->requireRights(UserRights::EDITOR, $this->getUserRights());
        // delete branch-specialization assignment by id
        $branchSpecAssignment = new BranchHasSpecialization($this->rootApp->getDatabase());
        $branchSpecAssignment->delete($id);
        $this->addLogEntry('delete', 'branchSpecAssignment', $id, 1, 'Branch-specialization assignment deleted: ID ' . $id);
        return json_encode(['message' => 'Branch-specialization assignment deleted successfully']);
    }
}

?>