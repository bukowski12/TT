<?php

use Nette\Application\UI\Form;


class SettingsPresenter extends BasePresenter
{

	/********************* view default *********************/


	public function renderDefault()
	{
		$config = $this->settings->findById(1);
		if ($config->config != "NULL") {
			$this->template->settings = unserialize($config->config);
		} else {
			$this->template->settings = NULL;
		}
	}

/********************* views add & edit *********************/


	public function renderEdit()
	{
		$form = $this['settingsForm'];
		if (!$form->isSubmitted()) {
			$settings = $this->settings->findById(1);
			if ($settings->config !="NULL") {
				$config = unserialize($settings->config);
				$form->setDefaults($config);
			}
		}
	}

/********************* view delete *********************/


	public function renderDelete($id = 0)
	{
		$this->template->tarif = $this->tarif->findById($id);
		if (!$this->template->tarif) {
			$this->error('Záznam nenalezen');
		}
	}


/********************* component factories *********************/


	/**
	 * Edit form factory.
	 * @return Form
	 */
	protected function createComponentSettingsForm()
	{
		$form = new Form;
		$form->addText('name', 'Název společnosti:')
			->setRequired('Zadej jméno.');

		$form->addText('email', 'Email:')
			->addCondition(Form::FILLED) //pokud je email vyplněn
		    ->addRule(Form::EMAIL, 'Prosím zadejte korektní e-mailovou adresu');

		$form->addText('pathconf', 'Cesta ke konfiguraci Promethea:')
			->setRequired('Zadej cestu.');

		$form->addText('hostsfile', 'Jméno souboru hosts:')
			->setRequired('Zadej jméno.');

		$form->addText('hostsinc', 'Jméno souboru hosts include:')
			->setRequired('Zadej jméno.');
			
		$form->addSubmit('save', 'Uložit')
			->setAttribute('class', 'default')
			->onClick[] = $this->settingsFormSucceeded;

		$form->addSubmit('cancel', 'Cancel')
			->setValidationScope(NULL)
			->onClick[] = $this->formCancelled;

		$form->addProtection();
		return $form;
	}

	public function settingsFormSucceeded($button)
		{
			$values = $button->getForm()->getValues();
			$config = serialize($values);
			$this->settings->findById(1)->update(array('config' => serialize($values)));
			$this->flashMessage('Nastavení bylo upraveno.','success');
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
		try {
			$this->tarif->findById($this->getParameter('id'))->delete();
			$this->flashMessage('Tarif byl smazán.','success');
		} catch(PDOException $e){
			if($e->getCode()==23000){
				if (strpos($e->getMessage(), '1451') !== FALSE) {
					$this->flashMessage('Tento tarif je používán, nejde smazat','error');
        		}
        	}elseif ($e->getCode()==42000)  $this->flashMessage($e->getMessage());
		else throw $e;
    	}
		$this->redirect('default');
	}


	public function formCancelled()
	{
		$this->redirect('default');
	}	
}