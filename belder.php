<?php
class DB
{

    public const SERVER = 'localhost';
    public const USERNAME = 'root';
    public const PASSWORD = '';
    public const DATABASE = 'crud';

    public $conn;
    private $table_name;
    private $columns    = [];
    private $values     = [];
    private $condition;
    private $limit;
    private $orderBy;
    private $groupBy;
    private $join;
    private $rightJoin;
    private $leftJoin;
    private $duplicate;
    private $columnCount;

    public $result;

    public function __construct()
    {

        try {
            $this->conn = new PDO("mysql:host=" . self::SERVER . ";dbname=" . self::DATABASE . "", self::USERNAME, self::PASSWORD);
        }
        // This code will appears error massage if disconnected to database
        catch (PDOException $e) {
            echo "ERROR: " . $e->getMessage();
        }
    }

    public function table(string $table_name): DB
    {
        $this->table_name = $table_name;
        return $this;
    }

    public function select(string ...$column_name): DB
    {
        $this->columns = $column_name;
        return $this;
    }

    public function orderBy(string $order, string ...$column_name): DB
    {
        $this->orderBy = implode(',', $column_name) . " $order";
        return $this;
    }

    public function groupBy(string ...$column_name): DB
    {
        $this->groupBy = implode(',', $column_name);
        return $this;
    }

    // This function for where statement with and
    public function where(string $column_name, string $operation, $value): DB
    {
        $condition = $column_name . " " . $operation . "  '$value'";

        $this->condition === null ?
            $this->condition = $condition :
            $this->condition .= ' AND ' . $condition;

        return $this;
    }


    // This function for where statement with or
    public function orWhere(string $column, string $operation, $value): DB
    {
        $condition = $column . " " . $operation . "  '$value'";
        $this->condition = $this->condition . ' OR ' . $condition;

        return $this;
    }

    public function getValue(...$values): DB
    {
        $this->values[] = $values;
        return $this;
    }



    public function limit($number, $to = null): DB
    {
        $toRecord = $to === null ? '' : ",$to";
        $this->limit = "$number" . $toRecord;

        return $this;
    }


    public function leftJoin(string $table_name, $FK, $PK): DB
    {
        $this->leftJoin = " LEFT JOIN  $table_name  ON  $FK  =  $PK";
        return $this;
    }



    public function rightJoin(string $table_name, $FK, $PK): DB
    {
        $this->rightJoin = " RIGHT JOIN  $table_name  ON  $FK  =  $PK";
        return $this;
    }

    public function join(string $table_name, $FK, $PK): DB
    {
        $this->join = " JOIN  $table_name  ON  $FK  =  $PK";
        return $this;
    }

    public function get()
    {
        $this->initializeStm();

        $sql = "SELECT " . $this->columns .
            " FROM " . $this->table_name
            . $this->join
            . $this->leftJoin
            . $this->rightJoin
            . $this->condition
            . $this->groupBy
            . $this->orderBy
            . $this->limit;

        $stm = $this->conn->prepare($sql);
        echo $sql;
        if ($stm->execute()) {
            $this->result = $stm->fetchAll(PDO::FETCH_OBJ);
        } else {
            $this->result = "error";
        }
    }

    // Function for update query
    public function update()
    {

        $this->initializeStm();

        $sql = "UPDATE " . $this->table_name
            . " SET "
            . $this->values
            . $this->condition;

        $this->conn->prepare($sql)->execute();

        $this->resetInput();
    }

    //count function for count records

    public function count(string $column = null, bool $duplicate = true)
    {
        $this->columnCount = $column;
        $this->duplicate   = $duplicate;

        $this->initializeStm();

        $sql = "SELECT COUNT (" . $column . " )" .
            " FROM " . $this->table_name
            . $this->condition
            . $this->orderBy;

        $stm = $this->conn->prepare($sql);
        if ($stm->execute()) {
            $this->result = $stm->fetchAll(PDO::FETCH_OBJ);
        }

        $this->resetInput();
    }

    // This function for initializing sql statement
    private function initializeStm()
    {
        $this->table_name = $this->table_name === null ? ''  : $this->table_name;

        $this->columns    = $this->columns === [] ? '*' : implode(', ', $this->columns);
        $this->values     = $this->values  === [] ? ''  : implode(', ', $this->values);

        $this->join      = $this->join      === null ? '' : $this->join;
        $this->rightJoin = $this->rightJoin === null ? '' : $this->rightJoin;
        $this->leftJoin  = $this->leftJoin  === null ? '' : $this->leftJoin;

        $this->condition = $this->condition === null ? ''  : " WHERE $this->condition ";
        $this->orderBy   = $this->orderBy   === null ? ''  : " ORDER BY $this->orderBy ";
        $this->limit     = $this->limit     === null ? ''  : " LIMIT $this->limit ";
        $this->groupBy   = $this->groupBy   === null ? ''  : " GROUP BY $this->groupBy ";

        $this->duplicate   = $this->duplicate   === true ? '' : 'DISTINCT';
        $this->columnCount = $this->columnCount === null ? " id " : "$this->duplicate  $this->columnCount";
    }

    private function resetInput()
    {
        $this->table_name = null;
        $this->columns    = [];
        $this->values     = [];

        $this->join      = null;
        $this->rightJoin = null;
        $this->leftJoin  = null;

        $this->condition   = null;
        $this->order       = null;
        $this->orderColumn = null;
        $this->limit       = null;

        $this->columnCount = null;
        $this->duplicate   = null;

        $this->result = [];
    }
}

// Select statement example
$DB_Query = new DB();
$DB_Query->table("gatCategory")
    ->select("*")->get();
echo "<hr>";
print_r($DB_Query->result);
$DB_Join = new DB();

//  JOIN statement example
$DB_Join->table('gatCategory')
    ->select('gatCategory.id', 'gatCategory.name', 'product.prodName')
    ->join('product', 'gatCategory.id', 'product.catID')
    ->get();

echo "<hr>";
echo "Example JOIN";
print_r($DB_Join->result);
echo "<hr>";





//  LEFT JOIN statement example

$DB_leftJoin = new DB();
$DB_leftJoin->table('gatCategory')
    ->select('gatCategory.id', 'gatCategory.name', 'product.prodName')
    ->leftJoin('product', 'gatCategory.id', 'product.catID')
    ->get();


echo "<hr>";
echo "Example JOIN";
print_r($DB_leftJoin->result);
echo "<hr>";

// Example RIGHT JOIN

$DB_rightJoin = new DB();
$DB_rightJoin->table('gatCategory')
    ->select('gatCategory.id', 'gatCategory.name', 'product.prodName')
    ->rightJoin('product', 'gatCategory.id', 'product.catID')
    ->get();

echo "<hr>";
echo "Example JOIN";
print_r($DB_rightJoin->result);
echo "<hr>";
