<?php

use Nette\Application\UI\Form;


class DashboardPresenter extends BasePresenter
{
	
	public function renderDefault()
	{
		$this->template->customer_count = $this->customer->findBy(array('valid' => '1'))->count();
		$this->template->client_count = $this->client->findBy(array('valid' => '1'))->count();
	}
}
