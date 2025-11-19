<?php
require_once __DIR__ . '/../../../core/Database.php';
require_once __DIR__ . '/../../../entities/BaseEntity.php';
require_once __DIR__ . '/../../../interfaces/ICrud.php';


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
        $result = $this->database->execute($query, $params);
        return $result !== false;
    }

    public function read($id=0): bool
    {
        $this->id = $id > 0 ? $id : $this->id;
        $query = '
        SELECT * FROM '.$this->getTableName().'
        WHERE
            id = ?;
        ';
        $params = [
            $this->id
        ];
        $result = $this->database->query($query, $params);
        if ($result && count($result) > 0)
        {
            $this->id = $result[0]['id'];
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
            branchId = ?, userId = ?,
            userRights = ?
        WHERE
            id = ?;
        ';
        $params = [
            $this->branchId,
            $this->userId,
            $this->userRights,
            $this->id
        ];
        $result = $this->database->execute($query, $params);
        return $result !== false;
    }

    public function delete($id = 0): bool
    {
        $this->id = $id > 0 ? $id : $this->id;
        $query = '
        DELETE FROM '.$this->getTableName().'
        WHERE
            id = ?;
        ';
        $params = [
            $this->id
        ];
        $result = $this->database->execute($query, $params);
        return $result !== false;
    }

    public function exists(): bool
    {
        return $this->branchId > 0 && $this->userId > 0;
    }

    public static function readAll(Database $database, string $sqlpostfix = ""): array
    {
        $query = '
        SELECT * FROM '.$database->getPrefix().self::$TABLE_NAME.'
        '.$sqlpostfix.';
        ';
        $result = $database->query($query);
        $entities = [];
        if ($result && count($result) > 0)
        {
            foreach ($result as $row)
            {
                $entity = new BranchHasUser($database);
                $entity->id = $row['id'];
                $entity->branchId = $row['branchId'];
                $entity->userId = $row['userId'];
                $entity->userRights = $row['userRights'];
                $entities[] = $entity;
            }
        }
        return $entities;
    }

	/** Database columns  *******************/
    public $id=0;
    public $branchId=0;
    public $userId=0;
    public $userRights=0;
    /** Database columns section ends ********/
}

?>