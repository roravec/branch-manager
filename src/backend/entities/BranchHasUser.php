<?php
require_once __DIR__ . '/../core/Database.php';
require_once __DIR__ . '/../entities/BaseEntity.php';
require_once __DIR__ . '/../interfaces/ICrud.php';


class BranchHasUser extends BaseEntity implements ICrud
{
    /**
     * Summary of TABLE_NAME
     * @var string
     */
	protected static $TABLE_NAME = "branchHasUser";

    public function create(): bool
    {
        $query = '
        INSERT INTO '.$this->getTableName().'
        (
            branchId, userId, userRights
        )
        VALUES (
            ?, ?, ?
        );
        ';
        $params = [
            $this->branchId,
            $this->userId,
            $this->userRights
        ];
        $result = $this->database->query($query, $params);
        return $result !== false;
    }

    public function read($id=0): bool
    {
        $query = '
        SELECT * FROM '.$this->getTableName().'
        WHERE
            branchId = ? AND userId = ?;
        ';
        $params = [
            $this->branchId,
            $this->userId
        ];
        $result = $this->database->query($query, $params);
        if ($result && count($result) > 0)
        {
            $this->branchId = $result[0]['branchId'];
            $this->userId = $result[0]['userId'];
            $this->userRights = $result[0]['userRights'];
            return true;
        }
        return false;
    }

    public function update(): bool
    {
        $query = '
        UPDATE '.$this->getTableName().'
        SET
            userRights = ?
        WHERE
            branchId = ? AND userId = ?;
        ';
        $params = [
            $this->userRights,
            $this->branchId,
            $this->userId
        ];
        $result = $this->database->query($query, $params);
        return $result !== false;
    }

    public function delete($id = 0): bool
    {
        $query = '
        DELETE FROM '.$this->getTableName().'
        WHERE
            branchId = ? AND userId = ?;
        ';
        $params = [
            $this->branchId,
            $this->userId
        ];
        $result = $this->database->query($query, $params);
        return $result !== false;
    }

	/** Database columns  *******************/
    public $branchId=0; // PRIMARY KEY
    public $userId=0;   // PRIMARY KEY
    public $userRights=0;
    /** Database columns section ends ********/
}

?>