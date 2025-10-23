<?php
require_once __DIR__ . '/../../../core/Database.php';
require_once __DIR__ . '/../../../entities/BaseEntity.php';
require_once __DIR__ . '/../../../interfaces/ICrud.php';


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
            name, coordinates, address, address2, description, employees, utilization
        )
        VALUES (
            ?, ?, ?, ?, ?, ?, ?
        );
        ';
        $params = [
            $this->name,
            $this->coordinates,
            $this->address,
            $this->address2,
            $this->description,
            $this->employees,
            $this->utilization
        ];
        $result = $this->database->execute($query, $params);
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
            $this->address2 = $result[0]['address2'];
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
            address2 = ?,
            description = ?,
            employees = ?,
            utilization = ?
        WHERE id = ?;
        ';
        $params = [
            $this->name,
            $this->coordinates,
            $this->address,
            $this->address2,
            $this->description,
            $this->employees,
            $this->utilization,
            $this->id
        ];
        $result = $this->database->execute($query, $params);
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
        $result = $this->database->execute($query, $params);
        return $result !== false;
    }

    public static function readAll($database): array
    {
        $query = '
        SELECT * FROM '.$database->getPrefix().self::$TABLE_NAME.';
        ';
        $result = $database->query($query);
        $branches = [];
        if ($result)
        {
            foreach ($result as $row)
            {
                $branch = new Branch($database);
                $branch->id = $row['id'];
                $branch->name = $row['name'];
                $branch->coordinates = $row['coordinates'];
                $branch->address = $row['address'];
                $branch->address2 = $row['address2'];
                $branch->description = $row['description'];
                $branch->employees = $row['employees'];
                $branch->utilization = $row['utilization'];
                $branches[] = $branch;
            }
        }
        return $branches;
    }

	/** Database columns  *******************/
	public $id=0;
	public $name="";
	public $coordinates="";
	public $address="";
	public $address2="";
	public $description="";
	public $employees=0;
	public $utilization=0;
    /** Database columns section ends ********/
}

?>