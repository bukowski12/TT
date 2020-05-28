<?php

use Nette\Application\UI\Form;


class RouterPresenter extends BasePresenter
{

/*
	protected function startup()
	{
		parent::startup();

		if (!$this->user->isLoggedIn()) {
			if ($this->user->logoutReason === Nette\Http\UserStorage::INACTIVITY) {
				$this->flashMessage('You have been signed out due to inactivity. Please sign in again.');
			}
			$this->redirect('Sign:in', array('backlink' => $this->storeRequest()));
		}
	}


	/********************* view default *********************/


	public function renderDefault($sort , $by )
	{
		if (isset($sort)) {
			if ($sort=='ipAddress'){
				$sort='INET_ATON(ipAddress)';
			}
			$this->template->router = $this->router->findAll()->order($sort." ".$by);
			$this->template->by = ($by == 'ASC') ? 'DESC' : 'ASC';
		} else {
			$this->template->router = $this->router->findAll()->order('name');
			$this->template->by = $by;
		}
	}

/********************* views add & edit *********************/


	public function renderAdd()
	{
		$this['routerForm']['save']->caption = 'Přidat';
	}

	public function renderAddInt($id = 0)
	{
		$this['routerIntForm']['save']->caption = 'Přidat';
		$this->template->router = $this->router->findById($id);
	}


	public function renderEdit($id = 0)
	{
		$form = $this['routerForm'];
		if (!$form->isSubmitted()) {
			$router = $this->router->findById($id);
			if (!$router) {
				$this->error('Vysílač nenalezen');
			}

			$form->setDefaults($router);
		}
	}

public function renderEditInt($idint = 0)
	{
		$form = $this['routerIntForm'];
		if (!$form->isSubmitted()) {
			$routerint = $this->routerint->findById($idint);
			if (!$routerint) {
				$this->error('Interface nenalezen');
			}

			$form->setDefaults($routerint);
		}
	}

/********************* view delete *********************/


	public function renderDelete($id = 0)
	{
		$this->template->router = $this->router->findById($id);
		if (!$this->template->router) {
			$this->error('Záznam nenalezen');
		}
	}

	public function renderDeleteInt($idint = 0)
	{
		$this->template->routerint = $this->routerint->findById($idint);
		if (!$this->template->routerint) {
			$this->error('Záznam nenalezen');
		}
	}

/********************* component factories *********************/


	/**
	 * Edit form factory.
	 * @return Form
	 */
	protected function createComponentRouterForm()
	{
		$form = new Form;
		$form->addText('name', 'Jméno:')
			->setRequired('Zadej jméno.');

		$form->addText('address', 'Adresa:')
			->setRequired('Zadej adresu.');
		
		$form->addText('gps', 'GPS souřadnice:');
			
		$form->addText('ipAddress', 'IP Adresa:')
			->setRequired('Zadej IP adresu.');
			
		$form->addText('netmask', 'Maska:')
			->setRequired('Zadej síťovou masku.');
			
		$form->addText('description', 'Popis:');
			
		$form->addSubmit('save', 'Uložit')
			->setAttribute('class', 'default')
			->onClick[] = $this->routerFormSucceeded;

		$form->addSubmit('cancel', 'Cancel')
			->setValidationScope(NULL)
			->onClick[] = $this->formCancelled;

		$form->addProtection();
		return $form;
	}

/**
	 * Edit form factory.
	 * @return Form
	 */
	protected function createComponentRouterIntForm()
	{
		$form = new Form;
		$form->addText('name', 'Název:')
			->setRequired('Zadej Název.');

		$interfaces = array(
			'Ethernet' => 'Ethernet',
    		'Wireless' => 'Wireless',
		);

		$form->addSelect('type', 'Rozhraní:', $interfaces);
			
		$form->addText('ipAddress', 'IP Adresa:')
			->setRequired('Zadej IP adresu.');
			
		$form->addText('netmask', 'Maska:')
			->setRequired('Zadej síťovou masku.');
			
		$form->addSubmit('save', 'Uložit')
			->setAttribute('class', 'default')
			->onClick[] = $this->routerIntFormSucceeded;

		$form->addSubmit('cancel', 'Cancel')
			->setValidationScope(NULL)
			->onClick[] = $this->formCancelled;

		$form->addProtection();
		return $form;
	}


	public function routerFormSucceeded($button)
		{
			$values = $button->getForm()->getValues();
			$id = (int) $this->getParameter('id');
			if ($id) {
				$this->router->findById($id)->update($values);
				$this->flashMessage('Vysílač byl upraven.','success');
				$this->logger->addLog($this->user->getIdentity()->getId(), 'I', 'Úprava vysílače '.$values['name']);
			} else {
				$this->router->insert($values);
				$this->flashMessage('Vysílač byl přidán.','success');
				$this->logger->addLog($this->user->getIdentity()->getId(), 'I', 'Přidání vysílače '.$values['name']);
			}
			$this->redirect('default');
		}

public function routerIntFormSucceeded($button)
		{
			try {
				$values = $button->getForm()->getValues();
				$idint = (int) $this->getParameter('idint');
				$values->router_id = (int) $this->getParameter('id');
				if ($idint) {
					$this->routerint->findById($idint)->update(array(
							'name' => $values->name,
							'type' => $values->type,
							'ipAddress' => $values->ipAddress,
							'netmask' => $values->netmask
					));
					$this->flashMessage('Rozhraní bylo upraveno.','success');
				} else {
					$this->routerint->insert($values);
					$this->flashMessage('Rozhraní bylo přidáno.','success');
				}
				$this->redirect('default');
			} catch(PDOException $e){
			if($e->getCode()==23000){
				if (strpos($e->getMessage(), '1062') !== FALSE) {
					$button->addError('Zadaná IP adresa již existuje','error');
        		}
        	}elseif ($e->getCode()==42000)  $this->error($e->getMessage(),'error');
			else throw $e;
		}
		}



/**
	 * Delete form factory.
	 * @return Form
	 */
	protected function createComponentDeleteForm()
	{
		$form = new Form;
		$form->addSubmit('cancel', 'Cancel')
			->onClick[] = $this->formCancelled;

		$form->addSubmit('delete', 'Delete')
			->setAttribute('class', 'default')
			->onClick[] = $this->deleteFormSucceeded;

		$form->addProtection();
		return $form;
	}

/**
	 * Delete form factory.
	 * @return Form
	 */
	protected function createComponentDeleteIntForm()
	{
		$form = new Form;
		$form->addSubmit('cancel', 'Cancel')
			->onClick[] = $this->formCancelled;

		$form->addSubmit('delete', 'Delete')
			->setAttribute('class', 'default')
			->onClick[] = $this->deleteIntFormSucceeded;

		$form->addProtection();
		return $form;
	}




	public function deleteFormSucceeded()
	{
		try {
			$this->router->findById($this->getParameter('id'))->delete();
			$this->flashMessage('Vysílač byl smazán.');
		} catch(PDOException $e){
			if($e->getCode()==23000){
				if (strpos($e->getMessage(), '1451') !== FALSE) {
					$this->flashMessage('Tento vysílač je používán, nejde smazat','error');
        		}
        	}elseif ($e->getCode()==42000)  $this->flashMessage($e->getMessage());
		else throw $e;
		}
		$this->redirect('default');
	}


	public function deleteIntFormSucceeded()
	{
		try {
			$this->routerint->findById($this->getParameter('idint'))->delete();
			$this->flashMessage('Rozhraní bylo smazáno.');
		} catch(PDOException $e){
			if($e->getCode()==23000){
				if (strpos($e->getMessage(), '1451') !== FALSE) {
					$this->flashMessage('Toto rozhraní je používáno, nejde smazat','error');
        		}
        	}elseif ($e->getCode()==42000)  $this->error($e->getMessage());
		else throw $e;
		}
		$this->redirect('default');
	}


	public function formCancelled()
	{
		$this->redirect('default');
	}	
}