<?php
require_once __DIR__ . '/../core/Database.php';
require_once __DIR__ . '/../entities/BaseEntity.php';
require_once __DIR__ . '/../interfaces/ICrud.php';


class BranchSpecialization extends BaseEntity implements ICrud
{
    /**
     * Summary of TABLE_NAME
     * @var string
     */
	protected static $TABLE_NAME = "branchSpecialization";

    public function create(): bool
    {
        $query = '
        INSERT INTO '.$this->getTableName().'
        (
            name, description
        )
        VALUES (
            ?, ?
        );
        ';
        $params = [
            $this->name,
            $this->description
        ];
        $result = $this->database->query($query, $params);
        return $result !== false;
    }

    public function read($id=0): bool
    {
        $query = '
        SELECT * FROM '.$this->getTableName().'
        WHERE
            id = ?;
        ';
        $params = [
            $id
        ];
        $result = $this->database->query($query, $params);
        if ($result && count($result) > 0)
        {
            $this->id = $result[0]['id'];
            $this->name = $result[0]['name'];
            $this->description = $result[0]['description'];
            return true;
        }
        return false;
    }

    public function update(): bool
    {
        $query = '
        UPDATE '.$this->getTableName().'
        SET
            name = ?, description = ?
        WHERE
            id = ?;
        ';
        $params = [
            $this->name,
            $this->description,
            $this->id
        ];
        $result = $this->database->query($query, $params);
        return $result !== false;
    }
    public function delete($id = 0): bool
    {
        $query = '
        DELETE FROM '.$this->getTableName().'
        WHERE
            id = ?;
        ';
        $params = [
            $id
        ];
        $result = $this->database->query($query, $params);
        return $result !== false;
    }

	/** Database columns  *******************/
    public $id=0;
    public $name="";
    public $description="";
    /** Database columns section ends ********/
}

?>