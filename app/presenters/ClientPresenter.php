<?php

use Nette\Application\UI\Form;
use Nette\Utils\Html;


class ClientPresenter extends BasePresenter
{


	public $search;
	public $invalid;


	public function renderDefault()
	{
		$this->template->invalid = ($this->invalid == 'checked')?'0':'1';
		if (!isset($this->template->customer)) {
			if ($this->search != NULL || $this->invalid != NULL) {
				$this->template->customer = $this->customer->findCustomerLike($this->search,'1')->order('surname')->order('name');
				$this['searchForm']['searchtext']->value = $this->search;
				$this['searchForm']['invalid']->value = $this->invalid;
			}else{
				$this->template->customer = $this->customer->findBy(array('valid' => '1'))->order('surname')->order('name');
			}
		}
	}


	public function handleSearch($value = NULL,$invalid_button = NULL)
	{
		$this->search = $value;
		$this->invalid = $invalid_button;
		$this->template->invalid = ($this->invalid == 'checked')?'0':'1';
		if (isset($value) || isset($invalid_button)) {
			$this->search = $value; 
			$this->invalidateControl('searchtable');
			$this->template->customer = $this->customer->findCustomerLike($value, '1')->order('surname')->order('name');
		}else{
			$this->invalidateControl('searchtable');
			$this->template->customer = $this->customer->findBy(array('valid' => '1'))->order('surname')->order('name');
		}
	}
/********************* views add & edit *********************/


	public function renderAdd($idcus = 0)
	{
		$this['clientForm']['save']->caption = 'Přidat';
		$customer = $this->customer->findById($idcus);
		if ($customer->__isset('address')) {
			$this['clientForm']['address']->value = $customer->address;
			$this['clientForm']['from']->value = $customer->from->format('Y-m-d');
			$this->template->customer = $customer;
		}
	}

	public function renderEdit($idcli = 0,$idcus = 0)
	{
		$form = $this['clientForm'];
		$customer = $this->customer->findById($idcus);
		$this->template->customer = $customer;
		if (!$form->isSubmitted()) {
			$client = $this->client->findById($idcli);
			if (!$client) {
				$this->error('Klient nenalezen');
			}
			$form->setDefaults($client);
			$this['clientForm']['from']->value = $client['from']->format('Y-m-d');
		}	
	}

	public function renderAddIp($idcli = 0)
	{
		$this['ipAddressForm']['save']->caption = 'Přidat';
		$interface = $this->client->findById($this->getParameter('idcli'))->routerint;
		$this->template->freeip = $this->listFreeIP($interface['ipAddress'],$interface['netmask']);
	}


	public function renderListIp($idcli = 0)
	{
		$this->template->ipaddress = $this->clientip->findBy(array('client_id' => $idcli))->order('INET_ATON(ipAddress)');
	}

	public function renderEditIp($idip = 0)
	{
		$form = $this['ipAddressForm'];
		if (!$form->isSubmitted()) {
			$clientip = $this->clientip->findById($idip);
			if (!$clientip) {
				$this->error('IP adresa nenalezena');
			}

			$form->setDefaults($clientip);
			if ($clientip['publicIP']) {
				$this['ipAddressForm']['publicIP']->value = $clientip['publicIP'];
				$this['ipAddressForm']['inactive']->value = TRUE;
			}
		$interface = $this->clientip->findById($this->getParameter('idip'))->client->routerint;
		$this->template->freeip = $this->listFreeIP($interface['ipAddress'],$interface['netmask']);
        }
	}

/********************* view delete *********************/


	public function renderDelete($id = 0)
	{
		$this->template->client = $this->client->findById($id);
		if (!$this->template->client) {
			$this->error('Záznam nenalezen');
		}
	}


 public function renderDeleteIp($id = 0)
	{
		$this->template->ipaddress = $this->clientip->findById($id);
		if (!$this->template->ipaddress) {
			$this->error('Záznam nenalezen');
		}
	}


/********************* component factories *********************/


	/**
	 * Edit form factory.
	 * @return Form
	 */
	protected function createComponentClientForm()
	{
		$form = new Form;
		
		$datum = new DateTime();

		$form->addText('address', 'Adresa:')
			->setRequired('Zadej adresu.');

		$form->addText('gps', 'GPS:');

		$form->addText('from', 'Datum aktivace:')
			->setType('date')
			->setRequired('Zadej datum.')
			->setDefaultValue($datum->format('Y-m-d'));

		$tarifs = $this->tarif->findAll()->select('idTarif, CONCAT(name, " - ", price," Kč") AS "name"')->order('price')->fetchPairs('idTarif','name');
		$form->addSelect('tarif_id', 'Tarif:', $tarifs)
			->setPrompt('Zvolte tarif')
			->setRequired('Vyber tarif.');

		$form->addText('discount', 'Sleva:')
			 ->addCondition(Form::FILLED) //pokud je email vyplněn
    		 ->addRule(Form::INTEGER, 'Sleva musí být číslo');

		$form->addSelect('routerInt_id', 'Router:',$this->getAllInterfaces())
			->setPrompt('Vyber router')
			->setRequired('Vyber router.');;

		$form->addText('hostname', 'Hostname:')
			->setRequired('Zadej Hostname je potřeba pro prometheus.');

		$form->addCheckbox('valid', 'Služba aktivní')
			->setDefaultValue(true);

		$form->addText('note', 'Poznámka:');

		$form->addSubmit('save', 'Uložit')
			->setAttribute('class', 'default')
			->onClick[] = $this->clientFormSucceeded;

		$form->addSubmit('cancel', 'Cancel')
			->setValidationScope(NULL)
			->onClick[] = $this->formCancelled;

		$form->addProtection();

		return $form;
	}

	public function clientFormSucceeded($button)
		{
			$values = $button->getForm()->getValues();
			$id = (int) $this->getParameter('idcli');
			$idcus = (int) $this->getParameter('idcus');
			$values['from'] = date( 'Y.m.d', strtotime($values['from']));
			$customer = $this->customer->findOneBy(array('idCustomer' => $idcus));
			try {
				if ($id) {
					$this->client->findById($id)->update(array(
							'customer_id' => $idcus,
							'address' => $values->address,
							'gps' => $values->gps,
							'from' => $values->from,
							'tarif_id' => $values->tarif_id,
							'discount' => $values->discount,
							'routerInt_id' => $values->routerInt_id,
							'note' => $values->note,
							'hostname' => $values->hostname,
							'valid' => $values->valid
						));
					$this->flashMessage('Služba Internet byla upravena.','success');
					$this->logger->addLog($this->user->getIdentity()->getId(), 'I', 'Služba Internet byla upravena zákazníkovi: '.$customer['surname']);
				} else {
					$lastclient = $this->client->insert(array(
							'customer_id' => $idcus,
							'address' => $values->address,
							'gps' => $values->gps,
							'from' => $values->from,
							'tarif_id' => $values->tarif_id,
							'discount' => $values->discount,
							'routerInt_id' => $values->routerInt_id,
							'note' => $values->note,
							'hostname' => $values->hostname,
							'valid' => $values->valid
						));
					$id = $lastclient['idClient'];
					$this->flashMessage('Služba Internet byla přidána.','success');
					$this->logger->addLog($this->user->getIdentity()->getId(), 'I', 'Služba Internet byla přidána zákazníkovi: '.$customer['surname']);
				}
				$this->restart = TRUE;
				$this->restoreRequest($this->backlink);
				$this->redirect('default');
			} catch(PDOException $e){
				if($e->getCode()==23000){
					if (strpos($e->getMessage(), '1062') !== FALSE) {
						$button->addError('Zadané hostname již existuje','error');
        			}
        		}elseif ($e->getCode()==42000)  $this->error($e->getMessage(),'error');
				else throw $e;
			}
		}


protected function createComponentIpAddressForm()
	{
		$form = new Form;
		
		$form->addText('ipAddress', 'IP adresa:')
			->setOption('description', Html::el('a href="#", id="trigger"')->setText('Volné adresy.'))
			->setRequired('Zadej IP adresu.');

		$form->addText('macAddress', 'MAC adresa:');

		$form->addCheckbox('inactive', 'Veřejná IP adresa');

		$form->addText('publicIP', 'Veřejná IP adresa')	
			->setOption("class", "off")
			->addConditionOn($form['inactive'], Form::EQUAL, TRUE)
        	// pak vyžaduj datum
        	->addRule(Form::FILLED, 'Zadejte veřejnou adresu');

		$form->addSubmit('save', 'Uložit')
			->setAttribute('class', 'default')
			->onClick[] = $this->ipAddressFormSucceeded;

		$form->addSubmit('cancel', 'Cancel')
			->setValidationScope(NULL)
			->onClick[] = $this->formCancelled;

		$form->addProtection();

		if ($this->getParameter('idcli') !== NULL) {
			$interface = $this->client->findById($this->getParameter('idcli'))->routerint;
		} else {
			$interface = $this->clientip->findById($this->getParameter('idip'))->client->routerint;
		}

		$form->onValidate[] = callback($this, 'validateIpAddressForm');
		return $form;
	}


	public function validateipAddressForm($form)
	{
		$values = $form->getValues(TRUE);
			/*if ($this->clientip->findOneBy(array('ipAddress' => $values['ipAddress'])) !== FALSE) {
				$form->addError('Zadaná IP adresa již existuje','error');
			}*/
		if(!filter_var($values['ipAddress'], FILTER_VALIDATE_IP)) {
			$form->addError('Ip adresa nemá správný formát.');
		}
        	if (!filter_var($values['publicIP'], FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE) && $values['inactive'] == TRUE) {
			$form->addError('Veřejná ip adresa nemá správný formát.');
		}
		if ($this->getParameter('idcli') !== NULL) {
                        $interface = $this->client->findById($this->getParameter('idcli'))->routerint;
		} else {
			$interface = $this->clientip->findById($this->getParameter('idip'))->client->routerint;
		}

        	if(!CIDR::IPisWithinCIDR($values['ipAddress'],$interface['ipAddress'].'/'.CIDR::maskToCIDR($interface['netmask']))) {
        		$form->addError('Ip adresa musí být z rozsahu: '.$interface['ipAddress'].'/'.CIDR::maskToCIDR($interface['netmask']));
        	}
	}


	public function ipAddressFormSucceeded($button)
		{
			$values = $button->getForm()->getValues();
			$id = (int) $this->getParameter('idip');
			$values->publicIP = (!$values->inactive) ? '' : $values->publicIP;
			foreach($values as $var => $value) {
        		if (isset($value) && $var!='inactive') $data[$var] = $value;

    		}
    		try {
				if ($id) {
					$redirect = $this->clientip->findById($id)->client->idClient;
					$this->clientip->findById($id)->update($data);
					$this->flashMessage('Ip adresa byla upravena.','success');
				} else {
					$redirect = $this->getParameter('idcli');
					$lastclient = $this->clientip->insert(array(
							'client_id' => (int) $this->getParameter('idcli'),
							'ipAddress' => $values->ipAddress,
							'publicIP' => $values->publicIP,
							'macAddress' => $values->macAddress,
						));
					$this->flashMessage('Ip adresa byla přidána.','success');
				}
				$this->restart = TRUE;
				dump ($this->restart);
				$this->restoreRequest($this->backlink);
				$this->redirect('listip',$redirect);
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


	public function deleteFormSucceeded()
	{
		try {
			$client = $this->client->findOneBy(array('idClient' => $this->getParameter('id')));
			$idcus = $client['customer_id'];
			$this->client->findById($this->getParameter('id'))->delete();
			$customer = $this->customer->findOneBy(array('idCustomer' => $idcus));
			$this->logger->addLog($this->user->getIdentity()->getId(), 'I', 'Služba Internet byla smazána zákazníkovi: '.$customer['surname']);
			$this->flashMessage('Služba Internet byla smazána.','success');

			$this->restart = TRUE;
		} catch(PDOException $e){
			if($e->getCode()==23000){
				if (strpos($e->getMessage(), '1451') !== FALSE) {
					$this->flashMessage('Přípojné místo má přiřazené IP adresy je potřeba je smazat','error');
        		}
        	}elseif ($e->getCode()==42000)  $this->flashMessage($e->getMessage());
		else throw $e;
    	}
		$this->restoreRequest($this->backlink);
		$this->redirect('default');
	}


	protected function createComponentDeleteIpAddressForm()
	{
		$form = new Form;
		$form->addSubmit('cancel', 'Cancel')
			->onClick[] = $this->formCancelled;

		$form->addSubmit('delete', 'Delete')
			->setAttribute('class', 'default')
			->onClick[] = $this->deleteIpAddressFormSucceeded;

		$form->addProtection();
		return $form;
	}

	public function deleteIpAddressFormSucceeded()
	{
		$this->clientip->findById($this->getParameter('id'))->delete();
		$this->flashMessage('IP adresa byla smazána.','success');
		$this->restart = TRUE;
		$this->restoreRequest($this->backlink);
		$this->redirect('default');
	}


	public function formCancelled()
	{
		$this->restoreRequest($this->backlink);
		$this->redirect('default');
	}	

	public function getAllInterfaces()
    {

        $routers = $this->router->findAll()->order('name')->fetchPairs('idRouter','name');
        $options = array();
        foreach ($routers as $idRouter => $name) {
        	$interfaces = $this->routerint->findBy(array('router_id' => $idRouter))->order('INET_ATON(ipAddress)');
        	foreach ($interfaces as $int) {
        		$options[$int->idRouterInt] = $name." - ".$int['name']." - ".$int['ipAddress']."/".CIDR::maskToCIDR($int['netmask']);
        	}
        }
        return $options;
    }

    public function listFreeIP($ip, $mask)
	{		
		$list = CIDR::cidrToList($ip.'/'.$mask);
		foreach ($list as $key => $value) {
			if ($this->clientip->findOneBy(array ('ipAddress' => $value))) {
				$listfree[$value] = 0;
			} else {
				$listfree[$value] = 1;
			}
		}
		return $listfree;
	}

	protected function createComponentSearchForm()
	{
		$form = new Form;
		$form->getElementPrototype()->class('ajax');
		
		$form->addText('searchtext', 'Hledej')
			->setType('search')
			->getControlPrototype()
			->onkeyup("$(this).ajaxSubmit();");

		$form->addCheckbox('invalid', 'Vypsat pozastavené');

        $form->onSuccess[] = callback($this, 'processSearchForm');
		return $form;
	}

	public function processSearchForm($form){

            $values = $form->values;
            $this->invalidateControl('searchtable');
            $this->template->customer = $this->client->findCustomerLike($values['searchtext'])->order('surname')->order('name');
        }



}
