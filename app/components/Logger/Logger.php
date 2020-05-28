<?php

use Nette\Application\UI\Presenter;

class Logger extends  Presenter
{
	protected $logger;

/** @var Nette\Database\Connection */
    protected $connection;

    public function __construct(Nette\Database\Connection $db)
    {
        $this->connection = $db;
    }

	public function addLog($user, $type, $desc)
	{
		$values = array('user_id' => $user, 'type' => $type, 'description' => $desc);
		//var_dump($values);
		//exit();
		$this->connection->table('log')->insert($values);
	}
} 