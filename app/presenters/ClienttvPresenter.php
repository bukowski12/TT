<?php

use Nette\Application\UI\Form;
use Nette\Utils\Html;


class ClienttvPresenter extends BasePresenter
{
	protected $sledovaniTv;

	protected function startup()
	{
		parent::startup();
		$this->sledovaniTv = new STV;
	}	

	public function renderDefault()
	{
		$this->template->clienttv = $this->clienttv->findAll();
	}

	public function renderAdd($idcus = 0)
	{
		$this['clientForm']['save']->caption = 'Přidat';
		$this->template->customer = $this->customer->findById($idcus);
#		if ($customer->__isset('address')) {
#			$this['clientForm']['from']->value = $customer->from->format('Y-m-d');
#			$this->template->customer = $customer;
#		}
	}

	public function renderEdit($idcus = 0, $idcli = 0)
	{
		$form = $this['clientForm'];
		$customer = $this->customer->findById($idcus);
		$this->template->customer = $customer;
		if (!$form->isSubmitted()) {
			$clienttv = $this->clienttv->findById($idcli);
			if (!$clienttv) {

				$this->error('Klient nenalezen');
			}
			$form->setDefaults($clienttv);
			$this['clientForm']['from']->value = $clienttv['from']->format('Y-m-d');
		}	
	}

/********************* view delete *********************/


	public function renderDelete($id = 0)
	{
		$this->template->clienttv = $this->clienttv->findById($id);
		if (!$this->template->clienttv) {
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

		$form->addText('from', 'Datum aktivace:')
			->setType('date')
			->setRequired('Zadej datum.')
			->setDefaultValue($datum->format('Y-m-d'));

		$tarifs = $this->tariftv->findAll()->select('idTariftv, CONCAT(name, " - ", price," Kč") AS "name"')->order('price')->fetchPairs('idTariftv','name');
		$form->addSelect('tariftv_id', 'Tarif:', $tarifs)
			->setPrompt('Zvolte tarif')
			->setRequired('Vyber tarif.');

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
			$response = $this->sledovaniTv->existUser($idcus);
			if ($response['status'] == 1) {
				$service = $this->tariftv->findOneBy(array('idTariftv' => $values->tariftv_id));
				$response = $this->sledovaniTv->activateUser($idcus, $service['apicode']);
				if ($response['status'] == 1) {
					if ($id) {
						$this->clienttv->findById($id)->update(array(
								'customer_id' => $idcus,
								'from' => $values->from,
								'tariftv_id' => $values->tariftv_id,
								'note' => $values->note,
							));
					} else {
						$lastclient = $this->clienttv->insert(array(
								'customer_id' => $idcus,
								'from' => $values->from,
								'tariftv_id' => $values->tariftv_id,
								'note' => $values->note,
							));
						$id = $lastclient['idClienttv'];
					}
				$this->flashMessage('Služba byla upravena.','success');
				} else {
					$text = 'Službu se nepodařilo nastavit. Odpověď API => '.$response['error'];	
					$this->flashMessage($text, 'error');
				}
			} else {	
				$text = 'Službu se nepodařilo nastavit. Odpověď API => '.$response['error'];	
				$this->flashMessage($text, 'error');
			}
			$this->restoreRequest($this->backlink);
			$this->redirect('default');
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
		$id = $this->getParameter('id');
		$clienttv = $this->clienttv->findOneBy(array('idClienttv' => $id));
		$idcus = $clienttv['customer_id'];
		$response = $this->sledovaniTv->existUser($idcus);
		if ($response['status'] == 1) {
			$response = $this->sledovaniTv->deactivateUser($idcus);
			if ($response['status'] == 1) {
				$this->clienttv->findById($id)->delete();
				$this->flashMessage('Služba byla smazána.','success');
			} else {
				$text = 'Službu se nepodařilo zrušit. Odpověď API => '.$response['error'];	
				$this->flashMessage($text, 'error');
			}
		} else {
		$text = 'Službu se nepodařilo zrušit. Odpověď API => '.$response['error'];	
		$this->flashMessage($text, 'error');
	}
		$this->restoreRequest($this->backlink);
		$this->redirect('default');
	}

	public function formCancelled()
	{
		$this->restoreRequest($this->backlink);
		$this->redirect('default');
	}	

}
