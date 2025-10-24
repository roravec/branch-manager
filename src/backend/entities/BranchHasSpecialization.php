<?php
require_once __DIR__ . '/../../../core/Database.php';
require_once __DIR__ . '/../../../entities/BaseEntity.php';
require_once __DIR__ . '/../../../interfaces/ICrud.php';


class BranchHasSpecialization extends BaseEntity implements ICrud
{
    /**
     * Summary of TABLE_NAME
     * @var string
     */
	protected static $TABLE_NAME = "branchHasSpecialization";

    public function create(): bool
    {
        $query = '
        INSERT INTO '.$this->getTableName().'
        (
            branchId, branchSpecializationId
        )
        VALUES (
            ?, ?
        );
        ';
        $params = [
            $this->branchId,
            $this->branchSpecializationId
        ];
        $result = $this->database->execute($query, $params);
        return $result !== false;
    }

    public function read($id=0): bool
    {
        $query = '
        SELECT * FROM '.$this->getTableName().'
        WHERE
            branchId = ? AND branchSpecializationId = ?;
        ';
        $params = [
            $this->branchId,
            $this->branchSpecializationId
        ];
        $result = $this->database->query($query, $params);
        if ($result && count($result) > 0)
        {
            $this->branchId = $result[0]['branchId'];
            $this->branchSpecializationId = $result[0]['branchSpecializationId'];
            return true;
        }
        return false;
    }

    public function update(): bool
    {
        $query = '
        UPDATE '.$this->getTableName().'
        SET
            branchId = ?, branchSpecializationId = ?
        WHERE
            branchId = ? AND branchSpecializationId = ?;
        ';
        $params = [
            $this->branchId,
            $this->branchSpecializationId,
            $this->branchId,
            $this->branchSpecializationId
        ];
        $result = $this->database->execute($query, $params);
        return $result !== false;
    }

    public function delete($id = 0): bool
    {
        $query = '
        DELETE FROM '.$this->getTableName().'
        WHERE
            branchId = ? AND branchSpecializationId = ?;
        ';
        $params = [
            $this->branchId,
            $this->branchSpecializationId
        ];
        $result = $this->database->execute($query, $params);
        return $result !== false;
    }

    public function exists(): bool
    {
        return $this->branchId > 0 && $this->branchSpecializationId > 0;
    }

    public static function readAll(Database $database, string $sqlpostfix = ""): array
    {
        $query = '
        SELECT * FROM '.self::$TABLE_NAME.'
        '.$sqlpostfix.';
        ';
        $params = [];
        $results = $database->query($query, $params);
        $entities = [];
        foreach ($results as $result)
        {
            $entity = new BranchHasSpecialization($database);
            $entity->branchId = $result['branchId'];
            $entity->branchSpecializationId = $result['branchSpecializationId'];
            $entities[] = $entity;
        }
        return $entities;
    }

	/** Database columns  *******************/
    public $branchId=0; // PRIMARY KEY
    public $branchSpecializationId=0; // PRIMARY KEY
    /** Database columns section ends ********/
}

?>