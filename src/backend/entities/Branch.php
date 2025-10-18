<?php
require_once __DIR__ . '/../core/Database.php';
require_once __DIR__ . '/../entities/BaseEntity.php';
require_once __DIR__ . '/../interfaces/ICrud.php';


class Branch extends BaseEntity implements ICrud
{
    /**
     * Summary of TABLE_NAME
     * @var string
     */
	protected static $TABLE_NAME = "branch";

    public function create(): bool
    {
        $query = '
        INSERT INTO 
        '.$this->getTableName().'
        (
            name, coordinates, address, description, employees, utilization
        )
        VALUES (
            ?, ?, ?, ?, ?, ?
        );
        ';
        $params = [
            $this->name,
            $this->coordinates,
            $this->address,
            $this->description,
            $this->employees,
            $this->utilization
        ];
        $result = $this->database->query($query, $params);
        return $result !== false;
    }

    public function read($id=0): bool
    {
        if ($id > 0)
        {
            $this->id = $id;
        }
        $query = '
        SELECT * FROM '.$this->getTableName().'
        WHERE id = ?;
        ';
        $params = [$this->id];
        $result = $this->database->query($query, $params);
        if ($result && count($result) > 0)
        {
            $this->id = $result[0]['id'];
            $this->name = $result[0]['name'];
            $this->coordinates = $result[0]['coordinates'];
            $this->address = $result[0]['address'];
            $this->description = $result[0]['description'];
            $this->employees = $result[0]['employees'];
            $this->utilization = $result[0]['utilization'];
            return true;
        }
        return false;
    }

    public function update(): bool
    {
        if ($this->id <= 0)
        {
            return false;
        }
        $query = '
        UPDATE '.$this->getTableName().'
        SET
            name = ?,
            coordinates = ?,
            address = ?,
            description = ?,
            employees = ?,
            utilization = ?
        WHERE id = ?;
        ';
        $params = [
            $this->name,
            $this->coordinates,
            $this->address,
            $this->description,
            $this->employees,
            $this->utilization,
            $this->id
        ];
        $result = $this->database->query($query, $params);
        return $result !== false;
    }

    public function delete($id = 0): bool
    {
        if ($id > 0)
        {
            $this->id = $id;
        }
        $query = '
        DELETE FROM '.$this->getTableName().'
        WHERE id = ?;
        ';
        $params = [$this->id];
        $result = $this->database->query($query, $params);
        return $result !== false;
    }

	/** Database columns  *******************/
	public $id=0;
	public $name="";
	public $coordinates="";
	public $address="";
	public $description="";
	public $employees=0;
	public $utilization=0;
    /** Database columns section ends ********/
}

?>