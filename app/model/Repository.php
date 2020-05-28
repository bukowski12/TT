<?php

/**
 * Provádí operace nad databázovou tabulkou.
 */
abstract class Repository extends Nette\Object
{
    /** @var Nette\Database\Connection */
    protected $connection;

    public function __construct(Nette\Database\Connection $db)
    {
        $this->connection = $db;
    }

    /**
     * Vrací objekt reprezentující databázovou tabulku.
     * @return Nette\Database\Table\Selection
     */
    protected function getTable()
    {
        // název tabulky odvodíme z názvu třídy
        preg_match('#(\w+)Repository$#', get_class($this), $m);
        return $this->connection->table(lcfirst($m[1]));
    }

    /**
     * Vrací všechny řádky z tabulky.
     * @return Nette\Database\Table\Selection
     */
    public function findAll()
    {
        return $this->getTable();
    }

    /**
     * Vrací řádky podle filtru, např. array('name' => 'John').
     * @return Nette\Database\Table\Selection
     */
    public function findBy(array $by)
    {
        return $this->getTable()->where($by);
    }

     /**
    * @param  array
    * @return \Nette\Database\Table\ActiveRow|FALSE
    */
    public function findOneBy(array $by)
    {
        return $this->findBy($by)->limit(1)->fetch();
    }

    public function findCustomerLike($like,$valid)
    {
        return $this->getTable()
        ->where('(name LIKE ? COLLATE utf8_general_ci OR surname LIKE ? COLLATE utf8_general_ci OR address LIKE ? COLLATE utf8_general_ci OR phone LIKE ? OR email LIKE ? OR vs LIKE ? OR company LIKE ? OR note LIKE ? COLLATE utf8_general_ci) AND valid = ?', "%".$like."%", "%".$like."%", "%".$like."%", "%".$like."%", "%".$like."%", "%".$like."%", "%".$like."%", "%".$like."%",$valid);
    }

    public function findCustomer($like,$searchcon)
    {
        if ($searchcon=='valid') {
            return $this->getTable()->where('(name LIKE ? COLLATE utf8_general_ci OR surname LIKE ? COLLATE utf8_general_ci OR address LIKE ? COLLATE utf8_general_ci OR phone LIKE ? OR email LIKE ? OR vs LIKE ? OR company LIKE ? OR note LIKE ? COLLATE utf8_general_ci) AND valid = 1', "%".$like."%", "%".$like."%", "%".$like."%", "%".$like."%", "%".$like."%", "%".$like."%", "%".$like."%", "%".$like."%");
        }elseif ($searchcon=='notvalid') {
            return $this->getTable()->where('(name LIKE ? COLLATE utf8_general_ci OR surname LIKE ? COLLATE utf8_general_ci OR address LIKE ? COLLATE utf8_general_ci OR phone LIKE ? OR email LIKE
            ? OR vs LIKE ? OR company LIKE ? OR note LIKE ? COLLATE utf8_general_ci) AND valid = 0', "%".$like."%", "%".$like."%", "%".$like."%", "%".$like."%", "%".$like."%", "%".$like."%", "%".$like."%", "%".$like."%");
        }elseif($searchcon=='warn') {
            return $this->getTable()->where('(name LIKE ? COLLATE utf8_general_ci OR surname LIKE ? COLLATE utf8_general_ci OR address LIKE ? COLLATE utf8_general_ci OR phone LIKE ? OR email LIKE 
            ? OR vs LIKE ? OR company LIKE ? OR note LIKE ? COLLATE utf8_general_ci) AND valid = 1 AND debtWarning IS NOT NULL AND debtlocked=0', "%".$like."%", "%".$like."%", "%".$like."%", "%".$like."%", "%".$like."%", "%".$like."%", "%".$like."%", "%".$like."%");
        }elseif($searchcon=='disabled') {
            return $this->getTable()->where('(name LIKE ? COLLATE utf8_general_ci OR surname LIKE ? COLLATE utf8_general_ci OR address LIKE ? COLLATE utf8_general_ci OR phone LIKE ? OR email LIKE 
            ? OR vs LIKE ? OR company LIKE ? OR note LIKE ? COLLATE utf8_general_ci) AND valid = 1 AND debtLocked=1', "%".$like."%", "%".$like."%", "%".$like."%", "%".$like."%", "%".$like."%", "%".$like."%", "%".$like."%", "%".$like."%");
        }
    }


    public function findById($id)
    {
        return $this->findAll()->get($id);
    }

    public function insert($values)
    {
        return $this->findAll()->insert($values);
    }

    public function findTodayTraffic($id,$ip)
    {
        $selection = $this->connection->table('traffic_log');
        return $selection->where('customer_id = ? AND ipaddress = ? AND period = ? AND creationDate >= DATE_ADD( CURDATE( ) , INTERVAL 3 HOUR )',$id,$ip,'h')->order('creationDate DESC')->limit(1);
    }

    public function findYesterdayTraffic($id,$ip)
    {
        $selection = $this->connection->table('traffic_log');
        return $selection->where('customer_id = ? AND ipaddress = ? AND period = ? AND DATE(creationDate) = DATE_SUB( CURDATE( ) , INTERVAL 1 DAY )',$id,$ip,'d')->order('creationDate DESC')->limit(1);
    }
}
