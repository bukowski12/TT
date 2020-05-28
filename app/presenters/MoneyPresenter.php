<?php

use Nette\Application\UI\Form;


class MoneyPresenter extends BasePresenter
{
	public $search;

	/********************* view default *********************/


	public function handleSearch($value = NULL)
	{
		$this->search = $value;
		if (isset($value)) {
			$this->search = $value; 
			$this->invalidateControl('searchtable');
			$this->template->customer = $this->customer->findCustomerLike($value, '1')->order('surname')->order('name');
		}else{
			$this->invalidateControl('searchtable');
			$this->template->customer = $this->customer->findBy(array('valid' => '1'))->order('surname')->order('name');
		}
	}


	public function renderDefault($sort = NULL , $by = NULL)
	{	
		$this->template->debit = $this->countDebit();
		$this->template->by = $by;
		if (!isset($this->template->customer)) {
			if ($this->search != NULL) {
				$this->template->customer = $this->customer->findCustomerLike($this->search,'1')->order('surname')->order('name');
				$this['searchForm']['searchtext']->value = $this->search;
				//$this->template->by = $by == 'ASC')
			}else{
				if (isset($sort)) {
					$this->template->customer = $this->customer->findBy(array('valid' => '1'))->order($sort." ".$by);
					$this->template->by = ($by == 'DESC') ? 'ASC' : 'DESC';
				} else {
					$this->template->customer = $this->customer->findBy(array('valid' => '1'))->order('surname')->order('name');
				}
			}
		}
	}

	public function renderPay($idcus = 0)
	{
		$this->template->customer = $this->customer->findbyId($idcus);
		$this->template->pay = $this->payment->findby(array('customer_id' => $idcus))->order("date DESC");
	}


	public function renderAdd($idcus = 0)
	{
		$this['payForm']['save']->caption = 'Přidat';
	}

	public function renderEdit($idcus = 0, $idpay = 0)
	{
		$form = $this['payForm'];
		if (!$form->isSubmitted()) {
			$payment = $this->payment->findById($idpay);
			if (!$payment) {
				$this->error('Platba nenalezena');
			}
			$form->setDefaults($payment);
			$this['payForm']['date']->value = $payment['date']->format('d.m.Y');
		}
	}

	public function renderDelete($idcus = 0, $idpay = 0)
	{
		$this->template->payment = $this->payment->findById($idpay);
		if (!$this->template->payment) {
			$this->error('Záznam nenalezen');
		}
	}

	public function renderBank(array $transfer)
	{
		$this->template->transfer = $transfer;
	}

	/********************* component factories *********************/


	/**
	 * Edit form factory.
	 * @return Form
	 */
	protected function createComponentPayForm()
	{
		$form = new Form;

		$datum = new DateTime();
		
		$type = array(
			'Cash' => 'Hotově',
    		'Bank' => 'Převodem',
    		'Fee'  => 'Poplatek za službu'	
		);

		$form->addSelect('type', 'Typ:', $type);

		$form->addText('date', 'Datum:')
			->setRequired('Zadej datum.')
			->setDefaultValue($datum->format('d.m.Y'));

		$form->addText('value', 'Platba:')
		    ->addRule(Form::INTEGER, 'Částka musí být číslo')
			->setRequired('Zadej platbu.');

		$form->addText('description', 'Popis:');	
		
		$form->addSubmit('save', 'Uložit')
			->setAttribute('class', 'default')
			->onClick[] = $this->payFormSucceeded;

		$form->addSubmit('cancel', 'Cancel')
			->setValidationScope(NULL)
			->onClick[] = $this->formCancelled;

		$form->addProtection();
		return $form;
	}

	public function payFormSucceeded($button)
		{
			$values = $button->getForm()->getValues();
			$values->customer_id = (int) $this->getParameter('idcus');
			$idpay = (int) $this->getParameter('idpay');
			$values['date'] = date( 'Y.m.d', strtotime($values['date']));
			if ($idpay) {
				$oldvalue = $this->payment->findById($idpay)->value;
				$diff = $oldvalue - $values->value;
				($diff > 0) ? $this->decBalance($values->customer_id,$diff) : $this->incBalance($values->customer_id,$diff);
				$this->payment->findById($idpay)->update($values);
				$this->flashMessage('Platba byla upravena.','success');
			} else {
				$this->payment->insert($values);
				($values->value > 0) ? $this->incBalance($values->customer_id,$values->value) : $this->decBalance($values->customer_id,$values->value);
				$this->flashMessage('Platba byla přidána.','success');
			}
			$this->restoreRequest($this->backlink);
			$this->redirect('pay', $this->getParameter('idcus'));
		}

	public function incBalance($id,$value)
		{
			$value = abs($value);
			$balance = $this->customer->findById($id)->balance;
			$balance = $balance + $value;
			$this->customer->findById($id)->update(Array('balance' => $balance));
		}

	public function decBalance($id,$value)
		{
			$balance = $this->customer->findById($id)->balance;
			$value = abs($value);
			$balance = $balance - $value;
			$this->customer->findById($id)->update(Array('balance' => $balance));
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
		$idpay = $this->getParameter('idpay');
		$value = $this->payment->findById($idpay)->value;
		$id = (int) $this->getParameter('idcus');
		$this->payment->findById($idpay)->delete();
		($value > 0) ? $this->decBalance($id,$value) : $this->incBalance($id,$value);
		$this->flashMessage('Platba byla smazána.','success');
		$this->restoreRequest($this->backlink);
		$this->redirect('pay', $this->getParameter('idcus'));
	}




	protected function createComponentBankForm()
	{
		$form = new Form;

		$form->addUpload('csvfile', 'CSV soubor z banky:')
			->setRequired('Zadej soubor.');
		
		$form->addSubmit('save', 'Nahrát')
			->setAttribute('class', 'default')
			->onClick[] = $this->bankFormSucceeded;

		$form->addSubmit('cancel', 'Cancel')
			->setValidationScope(NULL)
			->onClick[] = $this->formCancelled;

		$form->addProtection();
		return $form;
	}

	public function bankFormSucceeded($button)
	{
		$file = $button->getForm()->getValues();
		if ($file['csvfile']->isOk()) {
			$file_csv = fopen($file['csvfile']->getTemporaryFile(),"r");
			$transfer = array ();
			//cyklus který postupně přečte všechny řádky tabulky ...
			//... řádek potom rozdělí na jednotlivé hodnoty do pole $data
        	while ($data = fgetcsv ($file_csv, 1000, ";"))	{
                if (isset($data[9]))	{
                    $data[9]= str_replace( ' ', '',$data[9] );
                    $data[9]=(int)$data[9];
                    //preg_match("/([0-9]{10})/",$data[4], $data[4]);
                    //var_dump ($data);exit;
                    if ($data[7]!=NULL){
                    	$customer = $this->customer->findOneBy(array('vs' => $data[7]));
                    	if ($customer)	{
							echo "<tr>";         //nový řádek tabulky
							if (preg_match('~^([0-9]+)\-([0-9]+)\-([0-9]+)$~', $data[0], $match)) {  //prevod datumu
                               	$datum_iso = sprintf("%d-%02d-%02d", $match[3], $match[2], $match[1]);
							}
							if ($payment = $this->payment->findOneby(array(
								'customer_id' => $customer['idCustomer'], 
								'date' => 	$datum_iso,
								'value'	=> 	$data[9])))	{
								array_push($transfer, array (
									'status' => 'Platba už je jednou zapsána v DB',
									'error' => '2',
									'date' => $data[0],
									'vs' => $data[7],
									'kc' => $data[9]));
							}	else {
								$this->payment->insert(array(
								'customer_id' => $customer['idCustomer'],
								'type' => 'bank',
								'date' => 	$datum_iso,
								'value'	=> 	$data[9],
								'description' => 'Přišlo z účtu '.str_replace("'", "", $data[5])));
								$this->incBalance($customer['idCustomer'],$data[9]);
								array_push($transfer, array (
									'status' => 'Platba je v pořádku zapsána v DB',
									'error' => '0',
									'date' => $data[0],
									'vs' => $data[7],
									'kc' => $data[9]));
							}
						}else {
							array_push($transfer, array (
									'status' => 'Platbu není možné zapsat, uzivatel neni v DB nebo špatný VS',
									'error' => '1',
									'date' => $data[0],
									'vs' => $data[7],
									'kc' => $data[9]));
                    	}
                    }else{
                    	array_push($transfer, array (
									'status' => 'Platbu není možné zapsat, uzivatel nemá VS',
									'error' => '1',
									'date' => $data[0],
									'vs' => $data[7],
									'kc' => $data[9]));
					}
                }
	        }
		}
		$this->forward('bank',array($transfer));	
	}


	protected function createComponentSearchForm()
	{
		$form = new Form;
		$form->getElementPrototype()->class('ajax');
		
		$form->addText('searchtext', 'Hledej')
			->setType('search')
			->getControlPrototype()
			->onkeyup("$(this).ajaxSubmit();");

        $form->onSuccess[] = callback($this, 'processSearchForm');
		return $form;
	}

	public function processSearchForm($form)
	{

            $values = $form->values;
            $this->invalidateControl('searchtable');
            $this->template->customer = $this->customer->findCustomerLike($values['searchtext'])->order('surname')->order('name');
        }

	
	public function countDebit()
	{
		$money = 0;
		$suckers = $this->customer->findBy(array('balance < ?' => '0', 'valid' => '1'));
		foreach ($suckers as $cust) {
			$money = $money - $cust['balance'];
		}
		return (abs($money));
	}

	public function formCancelled()
	{
		$this->restoreRequest($this->backlink);
		$this->redirect('default');
	}	

}