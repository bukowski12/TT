<?php

use Nette\Application\UI\Form;
use Nette\Utils\Html;

class CustomerPresenter extends BasePresenter
{
	public $search;
	public $invalid;


	protected function createComponentPaginator()
	{
		$visualPaginator = new VisualPaginator();
		$visualPaginator->paginator->itemsPerPage = 100;
		return $visualPaginator;
	}

	public function renderDetail($id){
		$fee = 0;
        $this->template->customer = $this->customer->findOneBy(array('idCustomer' => $id));
        $clienttv = $this->clienttv->findOneBy(array('customer_id' => $id));
        $this->template->clienttv = $clienttv;
        $client = $this->client->findBy(array('customer_id' => $id));
        $this->template->client = $client;
        foreach ($client as $client_key) {
        	$tarif = $this->tarif->findOneBy(array('idTarif' => $client_key['tarif_id']));
        	$fee += $tarif['price'] - $client_key['discount'];
        	if ($client_key->related('clientIP','client_id')->count('*'))
				foreach ($client_key->related('clientIP','client_id') as $ip) {
					$today = $this->trafficlog->findTodayTraffic($id,$ip->ipAddress)->fetch();
					if ($today != FALSE) $todaylog[$ip->ipAddress] = array('down' => $today['down'],'up' => $today['up']);
					$yesterday = $this->trafficlog->findYesterdayTraffic($id,$ip->ipAddress)->fetch();
					if ($yesterday != FALSE) $yesterdaylog[$ip->ipAddress] = array('down' => $yesterday['down'],'up' => $yesterday['up']);

				}
        }
        $tariftv = $this->tariftv->findOneBy(array('idTariftv' => $clienttv['tariftv_id']));
        $fee += $tariftv['price'];
        $this->template->fee = $fee;
        if (isset($todaylog)) {
        	$this->template->todaylog = $todaylog;
        }
        if (isset($yesterdaylog)) {
        	$this->template->yesterdaylog = $yesterdaylog;
        }
       	$vp1 = new VisualPaginator($this, 'vp1');
    	$paginator1 = $vp1->getPaginator();
		$paginator1->itemsPerPage = 15;
		$paginator1->itemCount = $this->payment->findBy(array('customer_id' => $id))->count();
        $this->template->pay = $this->payment->findBy(array('customer_id' => $id))->limit( $paginator1->itemsPerPage,$paginator1->offset)->order("date DESC");
        $this->template->backlink = $this->storeRequest();
    }


	public function handleSearch($value = NULL,$searchcon = 'valid')
	{
		$paginator = $this['paginator']->getPaginator();
		$this->search = $value;
		//$this->invalid = $invalid_button;
		if (isset($value) || $searchcon!='valid') {
			$this->search = $value; 
			$paginator->itemCount = $this->customer->findCustomer($value, $searchcon)->count();
			$this->invalidateControl('searchtable');
			$this->template->customer = $this->customer->findCustomer($value, $searchcon)->order('surname')->order('name')->limit($paginator->itemsPerPage, $paginator->offset);
		}else {
			$paginator->itemCount = $this->customer->findBy(array('valid' => '1'))->count();
			$this->invalidateControl('searchtable');
			$this->template->customer = $this->customer->findBy(array('valid' => '1'))->order('surname')->order('name')->limit($paginator->itemsPerPage, $paginator->offset);
		}

	}


	/********************* view default *********************/


	public function renderDefault($sort = NULL , $by = NULL)
	{
		$this->template->by = $by;
		if (!isset($this->template->customer)) {
			$paginator = $this['paginator']->getPaginator();
			if ($this->search != NULL || $this->invalid != NULL) {
				$paginator->itemCount = $this->customer->findCustomerLike($this->search,'checked'?'0':'1')->count();
				$this->template->customer = $this->customer->findCustomerLike($this->search,'checked'?'0':'1')->order('surname')->order('name')->limit($paginator->itemsPerPage, $paginator->offset);
				$this['searchForm']['searchtext']->value = $this->search;
				$this['searchForm']['invalid']->value = $this->invalid;
			}else{
				// create visual paginator control
				$paginator = $this['paginator']->getPaginator();
				$paginator->itemCount = $this->customer->findAll()->count();
				if (isset($sort)) {
					$this->template->customer = $this->customer->findBy(array('valid' => '1'))->order($sort." ".$by)->limit($paginator->itemsPerPage, $paginator->offset);
					$this->template->by = ($by == 'DESC') ? 'ASC' : 'DESC';
				} else {
					$this->template->customer = $this->customer->findBy(array('valid' => '1'))->order('surname')->order('name')->limit($paginator->itemsPerPage, $paginator->offset);
				}
			}
		}
	}

/********************* views add & edit *********************/


	public function renderAdd()
	{
		$this['customerForm']['save']->caption = 'Přidat';
	}

	public function renderEdit($id = 0)
	{
		$form = $this['customerForm'];
		if (!$form->isSubmitted()) {
			$customer = $this->customer->findById($id);
			if (!$customer) {
				$this->error('Zákazník nenalezen');
			}
			$form->setDefaults($customer);
			$this['customerForm']['from']->value = $customer['from']->format('d.m.Y');
		}
	}

/********************* view delete *********************/


	public function renderDelete($id = 0)
	{
		$this->template->customer = $this->customer->findById($id);
		if (!$this->template->customer) {
			$this->error('Záznam nenalezen');
		}
	}


/********************* component factories *********************/


	/**
	 * Edit form factory.
	 * @return Form
	 */
	protected function createComponentCustomerForm()
	{
		$form = new Form;

		$datum = new DateTime();

		$form->addGroup(); // nutné, jinak by se ostatní skupiny zobrazily nad touto
		$form->addText('name', 'Jméno:')
			->setRequired('Zadej jméno.');

		$form->addText('surname', 'Příjmení:')
			->setRequired('Zadej příjmení.');

		$form->addText('address', 'Adresa:')
			->setRequired('Zadej adresu.');
		
		$form->addText('phone', 'Telefon:')
			->setRequired('Zadej telefon.');
			
		$form->addText('email', 'Email:')
			->addCondition(Form::FILLED) //pokud je email vyplněn
		    ->addRule(Form::EMAIL, 'Prosím zadejte korektní e-mailovou adresu');
			
		$form->addText('vs', 'VS:')
			->setDefaultValue('01'.$datum->format('dmy'))
			->setRequired('Zadej variabilní symbol.');
		
		$form->addText('from', 'Zákazník od:')
			->setRequired('Zadej datum.')
			->setDefaultValue($datum->format('d.m.Y'));
		
		$period = array(
			'1' => 'Měsíčně',
    		'3' => '3 měsíce',
    		'6' => '6 měsíců',
    		'12' => '12 měsíců',
		);

		$form->addSelect('payPeriod', 'Fakturační období:', $period);
		
		$form->addText('note', 'Poznámka:');
		
		$subjects = array(
			0 => "Fyzická osoba",
    		1 => "Právnická Osoba"
    		);

		$form->addRadioList("subject", "Jsem", $subjects)
			->setDefaultValue(0)
    		->addCondition(Form::EQUAL, 0)
        	->toggle("FO") // zobrazíme skupinu s id "PO"
	    	->elseCondition()
    	    ->toggle("PO"); // zobrazíme skupinu s id "FO"

		$form->addGroup()->setOption('container', Html::el('fieldset')->id("FO"));
		$form->addText("rc", "Rodné číslo");
		
		$form->addGroup()->setOption('container', Html::el('fieldset')->id("PO"));
	
		$form->addText('company', 'Firma:');

		$form->addText('ico', 'IČO:');

		$form->addText('dic', 'DIČ:');

		$form->addGroup();		
		$form->addCheckbox('valid', 'Platný')
			->setDefaultValue(true);

		$form->addSubmit('save', 'Uložit')
			->setAttribute('class', 'default')
			->onClick[] = $this->customerFormSucceeded;

		$form->addSubmit('cancel', 'Cancel')
			->setValidationScope(NULL)
			->onClick[] = $this->formCancelled;

		$form->addProtection();
		return $form;
	}

	public function customerFormSucceeded($button)
	{
		try {
			$values = $button->getForm()->getValues();
			$values['from'] = date( 'Y.m.d', strtotime($values['from']));
			$id = (int) $this->getParameter('id');
			if ($id) {
				$this->customer->findById($id)->update($values);
				$this->flashMessage('Zákazník byl upraven.','success');
				$this->logger->addLog($this->user->getIdentity()->getId(), 'I', 'Úprava zákazníka '.$values['surname']);
			} else {
				$lastcustomer = $this->customer->insert($values);
				$this->flashMessage('Zákazník byl přidán.','success');
				$id = $lastcustomer['idCustomer'];
				$this->logger->addLog($this->user->getIdentity()->getId(), 'I', 'Přidání zákazníka '.$values['surname']);
			}
			$this->restoreRequest($this->backlink);
			$this->redirect('detail', $id);
		} catch(PDOException $e){
			if($e->getCode()==23000){
				if (strpos($e->getMessage(), '1062') !== FALSE) {
					$button->addError('Zadaný VS již existuje','error');
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
		$this->customer->findById($this->getParameter('id'))->delete();
		$this->flashMessage('Zákazník byl smazán.','success');
		$this->redirect('default');
	}
	
	protected function createComponentSearchForm()
	{
		$form = new Form;
		$form->getElementPrototype()->class('ajax');
		
		$form->addText('searchtext', 'Hledej')
			->setType('search')
			->getControlPrototype()
			->onkeyup("$(this).ajaxSubmit();");

		$form->addCheckbox('invalid', 'Vypsat neplatné');

		$searchCon = array(
			'valid' => 'Platní zákazníci',
			'warn' => 'Varovaní zákazníci',
			'disabled' => 'Automaticky vypnutí zákazníci',
    		'notvalid' => 'Neplatní zákazníci'
		);

		$form->addSelect('searchcon', 'Vypsat:', $searchCon);

        $form->onSuccess[] = callback($this, 'processSearchForm');
		return $form;
	}

	public function processSearchForm($form){

            $values = $form->values;
            $this->invalidateControl('searchtable');
            $this->template->customer = $this->customer->findCustomerLike($values['searchtext'])->order('surname')->order('name');
        }

	public function formCancelled()
	{
		$this->restoreRequest($this->backlink);
		$this->redirect('default');
	}	
}
